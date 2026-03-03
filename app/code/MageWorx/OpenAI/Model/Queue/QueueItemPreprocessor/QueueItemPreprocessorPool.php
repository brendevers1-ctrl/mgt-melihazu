<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\QueueItemPreprocessor;

use MageWorx\OpenAI\Api\QueueItemPreprocessorInterface;
use MageWorx\OpenAI\Api\QueueItemPreprocessorPoolInterface;

class QueueItemPreprocessorPool implements QueueItemPreprocessorPoolInterface
{
    /**
     * @var array
     */
    protected array $preprocessors = [];

    /**
     * @param array $preprocessors
     */
    public function __construct(
        array $preprocessors = []
    ) {
        $this->preprocessors = $preprocessors;
    }

    /**
     * @param string $preprocessorCode
     * @return QueueItemPreprocessorInterface
     * @throws \InvalidArgumentException
     */
    public function getByType(string $preprocessorCode): QueueItemPreprocessorInterface
    {
        if (!isset($this->preprocessors[$preprocessorCode])) {
            throw new \InvalidArgumentException(
                sprintf('Preprocessor with code "%s" does not exist.', $preprocessorCode)
            );
        }

        return $this->preprocessors[$preprocessorCode];
    }
}
