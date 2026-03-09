<?php

namespace Custom\OrderToSheet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Custom\OrderToSheet\Helper\Data;
use Custom\OrderToSheet\Logger\Logger;

class InvoiceSuccess implements ObserverInterface
{
    protected $curl;
    protected $orderRepository;
    protected $helper;
    protected $logger;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Data $helper,
        Logger $logger
    ) {
        $this->curl = $curl;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {

        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $isEnabled = $this->helper->isEnabled();
        $webhook = $this->helper->getWebhook();
        if (!$isEnabled || !$webhook || $order->getData('sheet_pushed')) {
            return;
        }

        foreach ($order->getAllVisibleItems() as $item) {

            $data = $this->buildItemData($order, $item);


            $this->curl->post(
                "https://script.google.com/macros/s/YOUR_SCRIPT/exec",
                json_encode($data)
            );
        }

        // update flag
        $order->setData('sheet_pushed', 1);
        $this->orderRepository->save($order);
    }

    private function buildItemData($order, $item)
    {

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $productType = $item->getProductType();
        $variation_sku = '';
        if ($productType == 'configurable') {
            $productOptions = $item->getProductOptions();
            $variation_sku = $productOptions['simple_sku'];


        }
        return [

            "order_id" => $order->getId(),
            "order_status" => $order->getStatus(),
            "order_date" => $order->getCreatedAt(),
            "billing_first_name" => $billing->getFirstname(),
            "billing_last_name" => $billing->getLastname(),
            "full_name_billing" => $billing->getFirstname().' '.$billing->getLastname(),
            "billing_email" => $order->getCustomerEmail(),
            "billing_phone" => $billing->getTelephone(),
            "shipping_address_1" => $shipping->getStreetLine(1),
            "shipping_address_2" => $shipping->getStreetLine(2),
            "shipping_city" => $shipping->getCity(),
            "shipping_zipcode" => $shipping->getPostcode(),
            "shipping_state" => $shipping->getRegion(),
            "shipping_country" => $shipping->getCountryId(),
            "order_total" => $order->getGrandTotal(),
            "product_name" => $item->getName(),
            "product_id" => $item->getProductId(),
            "variation_id" => $item->getItemId(),
            "lineitem_sku" => $item->getSku(),
            "variation_sku" => $variation_sku,
            "quantity" => (int)$item->getQtyOrdered(),
            "unit_cost" => $item->getPrice(),
            "total_cost" => $item->getRowTotal(),
            "shipping_total" => $order->getShippingAmount(),
            "link_image" => '',
            "list_link_image" => '',
            "custom_text" => '',
            "customer_name" => $order->getCustomerName(),
            "customer_number" => ''
        ];
    }
}
