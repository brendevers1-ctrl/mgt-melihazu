<?php

namespace Custom\TrackingImport\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

class Process
{
    protected $resource;
    protected $orderFactory;
    protected $convertOrder;
    protected $transaction;
    protected $trackFactory;

    public function __construct(
        ResourceConnection $resource,
        OrderFactory $orderFactory,
        ConvertOrder $convertOrder,
        Transaction $transaction,
        TrackFactory $trackFactory
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->convertOrder = $convertOrder;
        $this->transaction = $transaction;
        $this->trackFactory = $trackFactory;
    }

    public function execute()
    {
        $connection = $this->resource->getConnection();

        $itemTable   = $this->resource->getTableName('custom_tracking_import_item');
        $importTable = $this->resource->getTableName('custom_tracking_import');

        // 🔥 Lấy tối đa 50 item pending
        $items = $connection->fetchAll("
            SELECT * FROM {$itemTable}
            WHERE status IS NULL OR status = 'pending'
            LIMIT 50
        ");
        if (empty($items)) {
            return;
        }

        $importIds = [];

        foreach ($items as $item) {

            $importIds[] = $item['import_id'];

            try {
                $orderIncrementId = $item['order_increment_id'];
                $trackingNumber   = $item['tracking_number'];

                // 🔍 Load order
                $order = $this->orderFactory->create()
                    ->loadByIncrementId($orderIncrementId);

                if (!$order->getId()) {
                    throw new \Exception('Order not found');
                }

                if (!$order->hasInvoices()) {
                    throw new \Exception('Order have not invoice yet');
                }

                if (!$order->canShip()) {
                    throw new \Exception('Order cannot be shipped');
                }

                // 🚀 Create shipment
                $shipment = $this->convertOrder->toShipment($order);

                foreach ($order->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }

                    $qty = $orderItem->getQtyToShip();

                    $shipmentItem = $this->convertOrder
                        ->itemToShipmentItem($orderItem)
                        ->setQty($qty);

                    $shipment->addItem($shipmentItem);
                }

                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);

                // 🚀 Add tracking
                $track = $this->trackFactory->create()
                    ->setCarrierCode('usps')
                    ->setTitle('USPS')
                    ->setTrackNumber($trackingNumber);

                $shipment->addTrack($track);

                // 💾 Save shipment + order
                $this->transaction
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

                // ✅ Update item SUCCESS
                $connection->update(
                    $itemTable,
                    [
                        'status' => 'success',
                        'message' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    ['entity_id = ?' => $item['entity_id']]
                );

            } catch (\Exception $e) {

                // ❌ Update item FAILED
                $connection->update(
                    $itemTable,
                    [
                        'status' => 'failed',
                        'message' => substr($e->getMessage(), 0, 255),
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    ['entity_id = ?' => $item['entity_id']]
                );
            }
        }

        // 🔁 UPDATE BẢNG CHA (IMPORT)
        $importIds = array_unique($importIds);

        foreach ($importIds as $importId) {

            // 📊 tổng số dòng
            $total = $connection->fetchOne("
                SELECT COUNT(*) FROM {$itemTable}
                WHERE import_id = {$importId}
            ");

            // ✅ success
            $success = $connection->fetchOne("
                SELECT COUNT(*) FROM {$itemTable}
                WHERE import_id = {$importId}
                AND status = 'success'
            ");

            // ❌ failed
            $failed = $connection->fetchOne("
                SELECT COUNT(*) FROM {$itemTable}
                WHERE import_id = {$importId}
                AND status = 'failed'
            ");

            // 🎯 status
            $status = 'processing';

            if (($success + $failed) >= $total) {
                $status = 'completed';
            }

            // 💾 update bảng cha
            $connection->update(
                $importTable,
                [
                    'processed_rows' => $success,
                    'status'       => $status,
                ],
                ['entity_id = ?' => $importId]
            );
        }
    }
}
