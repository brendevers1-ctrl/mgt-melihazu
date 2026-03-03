<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueProcessInterface;

interface QueueProcessManagementInterface
{
    /**
     * Get existing process by provided name
     *
     * @param string $name
     * @return QueueProcessInterface
     * @deprecared Use getExistingProcessByCode instead
     */
    public function getExistingProcessByName(string $name): QueueProcessInterface;

    /**
     * Get existing process by provided code
     *
     * @param string $code
     * @return QueueProcessInterface
     */
    public function getExistingProcessByCode(string $code): QueueProcessInterface;

    /**
     * Register new process and store it in the mageworx_openai_queue_process table.
     * Return the process object.
     * All queue items are grouped by the process id.
     * Process is used to display the progress bar in the admin panel.
     *
     * @param string $code
     * @param string $type
     * @param string $name
     * @param string $module
     * @param int $size
     * @param array|null $additionalData
     * @return QueueProcessInterface
     */
    public function registerProcess(
        string $code,
        string $type,
        string $name,
        string $module,
        int    $size,
        ?array $additionalData = []
    ): QueueProcessInterface;

    /**
     * @param int $processId
     * @return void
     */
    public function setQueueItemProcessed(int $processId): void;

    /**
     * @param string $code
     * @return void
     */
    public function setQueueItemProcessedByCode(string $code): void;
}
