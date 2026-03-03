<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\QueueItem;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Model\Queue\QueueItem as Model;
use MageWorx\OpenAI\Model\ResourceModel\DependencyChecker;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem as ResourceModel;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    public const MAX_ITEMS_PER_CHUNK = 100;

    protected DependencyChecker $dependencyChecker;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        DependencyChecker      $dependencyChecker,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    ) {
        $this->dependencyChecker = $dependencyChecker;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * Add filter to get only items with all dependencies ready
     *
     * @return Collection
     */
    public function addReadyDependenciesFilter(): Collection
    {
        $dependencyTable = $this->getTable('mageworx_openai_queue_dependencies');
        $queueItemTable  = $this->getMainTable();

        // We need to make sure the subquery selects dependencies of dependencies as well
        $subSelect = $this->getConnection()->select()
                          ->from(
                              ['dependency' => $dependencyTable],
                              ['dependency.dependency_item_id']
                          )
                          ->join(
                              ['queue_item' => $queueItemTable],
                              'queue_item.entity_id = dependency.dependency_item_id',
                              []
                          )
                          ->where('queue_item.status != ?', QueueItemInterface::STATUS_READY)
                          ->where('dependency.queue_item_id = main_table.entity_id');

        // Add NOT EXISTS condition to ensure we only get items with all dependencies ready
        $this->getSelect()->where('NOT EXISTS (?)', $subSelect);

        return $this;
    }

    /**
     * Adds a filter to the query to retrieve only active processes.
     * If status of a process is not set, it is considered active.
     *
     * @return $this Returns an instance of the Collection.
     */
    public function addActiveProcessFilter(): Collection
    {
        $this->getSelect()->join(
            ['process' => $this->getTable('mageworx_openai_queue_process')],
            'process.entity_id = main_table.process_id',
            []
        );

        $this->getSelect()->where(
            'process.status = ? OR process.status IS NULL',
            QueueProcessInterface::STATUS_ENABLED
        );

        return $this;
    }

    /**
     * Updates the status of queue items to "denied".
     *
     * @return int The number of items whose status was updated to "denied".
     */
    public function deny(): int
    {
        return $this->massUpdateStatus(QueueItemInterface::STATUS_DENIED);
    }

    /**
     * Updates the status of multiple items in the collection.
     *
     * @param int $status The new status to be set for the items.
     *
     * @return int The number of items that were updated.
     */
    public function massUpdateStatus(int $status): int
    {
        $ids   = $this->getAllIds();
        $count = 0;

        if (count($ids) > static::MAX_ITEMS_PER_CHUNK) {
            // Update in chunks of static::MAX_ITEMS_PER_CHUNK to avoid SQL errors
            $chunks = array_chunk($ids, static::MAX_ITEMS_PER_CHUNK);
            foreach ($chunks as $chunk) {
                $count += $this->_conn->update(
                    $this->getMainTable(),
                    ['status' => $status],
                    ['entity_id IN (?)' => $chunk]
                );
            }
        } else {
            $count = $this->_conn->update(
                $this->getMainTable(),
                ['status' => $status],
                ['entity_id IN (?)' => $ids]
            );
        }

        return $count;
    }

    public function addPreparedResponseField(): void
    {
        // Add field to select with alias 'prepared_response' which will be the 'content' property of JSON column 'response'
        $this->addExpressionFieldToSelect(
            'prepared_response',
            new \Zend_Db_Expr(
                sprintf(
                    'JSON_UNQUOTE(JSON_EXTRACT(%s, \'$."content"\'))',
                    $this->getConnection()->quoteIdentifier('response')
                )
            ),
            []
        );
    }
}

