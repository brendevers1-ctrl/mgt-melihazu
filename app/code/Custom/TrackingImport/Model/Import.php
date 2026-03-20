<?php
namespace Custom\TrackingImport\Model;

use Magento\Framework\Model\AbstractModel;

class Import extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Custom\TrackingImport\Model\ResourceModel\Import::class);
    }
}
