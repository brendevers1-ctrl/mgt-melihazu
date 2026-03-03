<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog;

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
            \MageWorx\SeoAI\Model\ProductRequestResponseLog::class,
            \MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog::class
        );
        $this->_setIdFieldName('entity_id');
    }
}
