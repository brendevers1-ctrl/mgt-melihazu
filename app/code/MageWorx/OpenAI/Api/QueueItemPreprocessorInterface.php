<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Exception\QueueItemPreprocessingException;

interface QueueItemPreprocessorInterface
{
    /**
     * Prepare queue item data before sending request to OpenAI API.
     * Starting point for correction prompt, adding completion options, etc.
     *
     * @param QueueItemInterface $queueItem
     * @return QueueItemInterface
     * @throws QueueItemPreprocessingException
     */
    public function execute(QueueItemInterface $queueItem): QueueItemInterface;
}
