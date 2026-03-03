<?php

namespace MageWorx\OpenAI\Model\Models;

use MageWorx\DeliveryDate\Model\Queue;
use MageWorx\OpenAI\Api\MessengerInterface;
use MageWorx\OpenAI\Api\QueueManagementInterface as QueueManagement;
use MageWorx\OpenAI\Api\ModelInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\RequestInterface;

abstract class AbstractModel implements ModelInterface
{
    /** @var string Model type */
    protected string $type;

    /** @var string Path to where we send request (without leading slash) */
    protected string $path = 'v1/chat/completions';

    protected int $maxContextLength = 512;

    protected bool $useMaxTokens = false;

    /** @var MessengerInterface Send messages and return response */
    protected MessengerInterface $messenger;

    /** @var QueueManagement Add message to queue */
    protected QueueManagement $queueManagement;

    public function __construct(
        MessengerInterface $messenger,
        QueueManagement    $queueManagement
    ) {
        $this->messenger       = $messenger;
        $this->queueManagement = $queueManagement;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $value
     * @return ModelInterface
     */
    public function setPath(string $value): ModelInterface
    {
        $this->path = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Maximum allowed context length for this model
     *
     * @return int
     */
    public function getMaxContextLength(): int
    {
        return $this->maxContextLength;
    }

    /**
     * Is max_tokens parameter should be used for this model
     *
     * @return bool
     */
    public function useMaxTokens(): bool
    {
        return $this->useMaxTokens;
    }

    /**
     * @param RequestInterface $request
     * @param OptionsInterface $options
     * @return int
     */
    public function addToQueue(RequestInterface $request, OptionsInterface $options): int
    {
        $options->setModel($this->getType());
        $options->setPath($request->getPath());

        $content = $request->getContent();
        $context = $request->getContext();
        $callback = $request->getCallback();

        // TODO: Add process
        // TODO: Add additional data

        $queueItem = $this->queueManagement->addToQueue($content, $options, $callback, $context);

        return $queueItem->getId();
    }

    /**
     * Content message must be always string
     *
     * @param mixed $content
     * @return string
     */
    protected function prepareMessageContent($content): string
    {
        if (!is_string($content)) {
            $content = json_encode($content);
        }

        return $content;
    }
}
