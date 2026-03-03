<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue;

use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterfaceFactory as QueueProcessFactory;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess as QueueProcessResource;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\CollectionFactory as QueueProcessCollectionFactory;

class QueueProcessManagement implements QueueProcessManagementInterface
{
    protected QueueProcessFactory           $queueProcessFactory;
    protected QueueProcessResource          $queueProcessResource;
    protected QueueProcessCollectionFactory $queueCollectionFactory;

    protected Json $jsonSerializer;

    public function __construct(
        QueueProcessFactory           $queueProcessFactory,
        QueueProcessResource          $queueProcessResource,
        QueueProcessCollectionFactory $queueCollectionFactory,
        Json                          $jsonSerializer
    ) {
        $this->queueProcessFactory    = $queueProcessFactory;
        $this->queueProcessResource   = $queueProcessResource;
        $this->queueCollectionFactory = $queueCollectionFactory;

        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @inheritDoc
     */
    public function getExistingProcessByName(string $name): QueueProcessInterface
    {
        /** @var QueueProcessInterface $process */
        $process = $this->queueProcessFactory->create();

        $collection = $this->queueCollectionFactory->create();
        $collection->addFieldToFilter('name', $name)
                   ->setPageSize(1)
                   ->setCurPage(1);

        if ($collection->getSize() > 0) {
            $process = $collection->getFirstItem();
        }

        return $process;
    }

    /**
     * @inheritDoc
     */
    public function getExistingProcessByCode(string $code): QueueProcessInterface
    {
        /** @var QueueProcessInterface $process */
        $process = $this->queueProcessFactory->create();

        $collection = $this->queueCollectionFactory->create();
        $collection->addFieldToFilter('code', $code)
                   ->setPageSize(1)
                   ->setCurPage(1);

        if ($collection->getSize() > 0) {
            $process = $collection->getFirstItem();
        }

        return $process;
    }

    /**
     * @inheritDoc
     */
    public function registerProcess(
        string $code,
        string $type,
        string $name,
        string $module,
        int    $size,
        ?array $additionalData = []
    ): QueueProcessInterface {
        // Detect process: take existing or create new
        try {
            $process = $this->getExistingProcessByCode($code);
        } catch (\Exception $e) {
            /** @var QueueProcessInterface $process */
            $process = $this->queueProcessFactory->create();
        }

        // Merge additional data if process exists
        $existingAdditionalData = $process->getAdditionalData();
        if ($existingAdditionalData) {
            $existingAdditionalData = $this->jsonSerializer->unserialize($existingAdditionalData);
            $additionalData         = array_merge($existingAdditionalData, $additionalData);
        }

        $additionalDataAsJson = $this->jsonSerializer->serialize($additionalData);

        // Update process data
        $process->setCode($code)
                ->setType($type)
                ->setName($name)
                ->setModule($module)
                ->setSize($process->getSize() + $size)
                ->setAdditionalData($additionalDataAsJson);

        // Save new or updated process
        $this->queueProcessResource->save($process);

        // Return process to caller
        return $process;
    }

    /**
     * @inheritDoc
     */
    public function setQueueItemProcessed(int $processId): void
    {
        /** @var QueueProcessInterface $process */
        $process = $this->queueProcessFactory->create();
        $this->queueProcessResource->load($process, $processId);

        $process->setProcessed($process->getProcessed() + 1);
        $this->queueProcessResource->save($process);
    }

    /**
     * @inheritDoc
     */
    public function setQueueItemProcessedByCode(string $code): void
    {
        /** @var QueueProcessInterface $process */
        $process = $this->queueProcessFactory->create();
        $this->queueProcessResource->load($process, $code, 'code');

        $process->setProcessed($process->getProcessed() + 1);
        $this->queueProcessResource->save($process);
    }
}
