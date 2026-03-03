<?php

declare(strict_types=1);

namespace MageWorx\OpenAI\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QueueProcess extends AbstractDb
{
    /**
     * Define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('mageworx_openai_queue_process', 'entity_id');
    }
}
