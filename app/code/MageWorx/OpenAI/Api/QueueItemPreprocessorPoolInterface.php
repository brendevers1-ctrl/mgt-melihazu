<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

interface QueueItemPreprocessorPoolInterface
{
    /**
     * Get preprocessor from pool by its type
     *
     * @param string $preprocessorCode
     * @return QueueItemPreprocessorInterface
     * @throws \InvalidArgumentException
     */
    public function getByType(string $preprocessorCode): QueueItemPreprocessorInterface;
}
