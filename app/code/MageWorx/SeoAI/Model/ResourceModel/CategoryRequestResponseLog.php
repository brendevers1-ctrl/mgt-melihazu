<?php

namespace MageWorx\SeoAI\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class CategoryRequestResponseLog extends AbstractDb
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
        $this->_init('mageworx_category_rr_log', 'entity_id');
    }
}
