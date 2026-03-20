<?php
namespace Custom\TrackingImport\Model\ResourceModel\Import;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Custom\TrackingImport\Model\Import::class,
            \Custom\TrackingImport\Model\ResourceModel\Import::class
        );
    }
}
