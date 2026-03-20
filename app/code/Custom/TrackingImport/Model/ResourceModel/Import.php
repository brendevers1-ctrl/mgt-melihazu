<?php
namespace Custom\TrackingImport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Import extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_tracking_import', 'entity_id');
    }
}
