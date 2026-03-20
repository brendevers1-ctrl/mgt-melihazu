<?php

namespace Custom\TrackingImport\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\File\Csv;
use Magento\Framework\App\ResourceConnection;

class Upload extends Action
{
    protected $csv;
    protected $resource;

    public function __construct(
        Action\Context $context,
        Csv $csv,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->csv = $csv;
        $this->resource = $resource;
    }

    public function execute()
    {
        try {
            // 🔒 Validate form key
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key'));
            }

            // 📂 Check file upload
            if (empty($_FILES['csv_file']['tmp_name'])) {
                throw new LocalizedException(__('Please upload a CSV file'));
            }

            $file = $_FILES['csv_file']['tmp_name'];
            $name = $_FILES['csv_file']['name'];
            // 📄 Read CSV
            $data = $this->csv->getData($file);

            if (count($data) <= 1) {
                throw new LocalizedException(__('CSV file is empty or invalid'));
            }

            $connection = $this->resource->getConnection();

            $importTable = $this->resource->getTableName('custom_tracking_import');
            $itemTable   = $this->resource->getTableName('custom_tracking_import_item');

            // 🚀 1. Create import session (parent)
            $connection->insert($importTable, [
                'total_rows' => count($data) - 1,
                'file_name' => $name,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $importId = $connection->lastInsertId();

            $success = 0;
            $failed  = 0;

            // ❌ remove header
            unset($data[0]);

            // 🚀 2. Insert items
            foreach ($data as $row) {

                // validate data
                if (empty($row[0]) || empty($row[1])) {
                    $failed++;
                    continue;
                }

                try {
                    $connection->insert($itemTable, [
                        'import_id'           => $importId,
                        'order_increment_id' => trim($row[0]),
                        'tracking_number'    => trim($row[1]),
                        'created_at'         => date('Y-m-d H:i:s')
                    ]);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }

            // ✅ Success message
            $this->messageManager->addSuccessMessage(__(
                'Import completed. Success: %1, Failed: %2',
                $success,
                $failed
            ));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('trackingimport/import/index');
    }

    protected function _isAllowed()
    {
        return true;
    }
}
