<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;

class DependencyChecker extends AbstractDb
{
    protected function _construct()
    {
        // Initialize the DependencyChecker resource model
        $this->_init('mageworx_openai_queue_dependencies', 'id');
    }

    /**
     * Check if all dependencies for a given queue item are ready
     *
     * @param int $queueItemId
     * @return bool
     * @throws LocalizedException
     */
    public function areDependenciesReady(int $queueItemId): bool
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['d' => $this->getMainTable()], [])
                                 ->join(
                                     ['q' => $this->getTable('mageworx_openai_queue_item')],
                                     'd.dependency_item_id = q.entity_id',
                                     []
                                 )
                                 ->where('d.queue_item_id = ?', $queueItemId)
                                 ->where('q.status != ?', QueueItemInterface::STATUS_READY);

        $result = $connection->fetchCol($select);

        // If there are no rows, it means all dependencies are ready
        return empty($result);
    }

    /**
     * @param int $queueItemId
     * @param int[] $dependencyItemsIds
     * @return void
     * @throws LocalizedException
     */
    public function createDependency(int $queueItemId, array $dependencyItemsIds): void
    {
        $connection = $this->getConnection();
        $data       = [];
        foreach ($dependencyItemsIds as $dependencyItemId) {
            $data[] = [
                'queue_item_id'      => $queueItemId,
                'dependency_item_id' => $dependencyItemId
            ];
        }

        $connection->insertMultiple($this->getMainTable(), $data);
    }
}
