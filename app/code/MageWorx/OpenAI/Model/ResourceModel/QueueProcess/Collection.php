<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\QueueProcess;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageWorx\OpenAI\Model\Queue\QueueProcess as Model;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess as ResourceModel;

class Collection extends AbstractCollection
{
    public const MAX_ITEMS_PER_CHUNK = 100;

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * Updates the status of multiple entities to enable.
     *
     * @return int The number of entities whose status was updated.
     */
    public function massRun(): int
    {
        $ids   = $this->getAllIds();
        $count = 0;

        if (count($ids) > static::MAX_ITEMS_PER_CHUNK) {
            // Update in chunks of static::MAX_ITEMS_PER_CHUNK to avoid SQL errors
            $chunks = array_chunk($ids, static::MAX_ITEMS_PER_CHUNK);
            foreach ($chunks as $chunk) {
                $count += $this->_conn->update(
                    $this->getMainTable(),
                    ['status' => Model::STATUS_ENABLED],
                    ['entity_id IN (?)' => $chunk]
                );
            }
        } else {
            $count = $this->_conn->update(
                $this->getMainTable(),
                ['status' => Model::STATUS_ENABLED],
                ['entity_id IN (?)' => $ids]
            );
        }

        return $count;
    }

    /**
     * Mass stops the entities by updating their status to disable.
     *
     * @return int The number of entities that were successfully stopped.
     */
    public function massStop(): int
    {
        $ids   = $this->getAllIds();
        $count = 0;

        if (count($ids) > static::MAX_ITEMS_PER_CHUNK) {
            // Update in chunks of static::MAX_ITEMS_PER_CHUNK to avoid SQL errors
            $chunks = array_chunk($ids, static::MAX_ITEMS_PER_CHUNK);
            foreach ($chunks as $chunk) {
                $count += $this->_conn->update(
                    $this->getMainTable(),
                    ['status' => Model::STATUS_DISABLED],
                    ['entity_id IN (?)' => $chunk]
                );
            }
        } else {
            $count = $this->_conn->update(
                $this->getMainTable(),
                ['status' => Model::STATUS_DISABLED],
                ['entity_id IN (?)' => $ids]
            );
        }

        return $count;
    }

    /**
     * Mass deletes the entities by removing them from the database.
     *
     * @return int The number of entities that were successfully deleted.
     */
    public function massDelete(): int
    {
        $ids   = $this->getAllIds();
        $conn  = $this->getConnection();
        $count = 0;

        if (count($ids) > static::MAX_ITEMS_PER_CHUNK) {
            // Delete in chunks of static::MAX_ITEMS_PER_CHUNK to avoid SQL errors
            $chunks = array_chunk($ids, static::MAX_ITEMS_PER_CHUNK);
            foreach ($chunks as $chunk) {
                $count += $conn->delete(
                    $this->getMainTable(),
                    ['entity_id IN (?)' => $chunk]
                );
            }
        } else {
            // Delete all at once
            $count = $conn->delete(
                $this->getMainTable(),
                ['entity_id IN (?)' => $ids]
            );
        }

        return $count;
    }

    /**
     * Adds a processed_percent column to the collection.
     *
     * This method adds a processed_percent column to the collection based on the size and processed columns.
     * The column name will be processed_percent and its value will be calculated as the percentage of processed
     * items over the total size, rounded to two decimal places.
     *
     * @return void
     */
    public function addProcessedPercentColumn(): void
    {
        // Add column to collection which is based on size and processed columns
        // Column name will be processed_percent
        $this->addExpressionFieldToSelect(
            'processed_percent',
            'ROUND((main_table.processed / main_table.size) * 100, 2)',
            []
        );
    }
}
