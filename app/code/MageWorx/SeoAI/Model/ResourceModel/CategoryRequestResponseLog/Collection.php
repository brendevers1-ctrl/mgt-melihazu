<?php

namespace MageWorx\SeoAI\Model\ResourceModel\CategoryRequestResponseLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageWorx\SeoAI\Model\CategoryRequestResponseLog::class,
            \MageWorx\SeoAI\Model\ResourceModel\CategoryRequestResponseLog::class
        );
        $this->_setIdFieldName('entity_id');
    }
}
