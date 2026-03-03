<?php

namespace MageWorx\OpenAI\Model\Queue\Callback;

use Magento\Framework\ObjectManagerInterface;
use MageWorx\OpenAI\Api\CallbackInterface;

class CallbackFactory
{
    private ObjectManagerInterface $objectManager;
    private array                  $callbacks;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array                  $callbacks = []
    ) {
        $this->objectManager = $objectManager;
        $this->callbacks     = $callbacks;
    }

    public function create(string $type): CallbackInterface
    {
        if (!isset($this->callbacks[$type])) {
            throw new \InvalidArgumentException('Callback type "' . $type . '" is not defined.');
        }

        return $this->objectManager->create($this->callbacks[$type]);
    }
}
