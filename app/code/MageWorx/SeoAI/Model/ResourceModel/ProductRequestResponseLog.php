<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class ProductRequestResponseLog extends AbstractDb
{
    /**
     * Serializable fields
     *
     * @var array
     */
    protected $_serializableFields = [
        'context' => [[], []]
    ];

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_product_rr_log', 'entity_id');
    }
}
