<?php

namespace MageWorx\OpenAI\Api;

interface ModelInterface
{
    /**
     * Type of selected model
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get maximum allowed length of context
     *
     * @return int
     */
    public function getMaxContextLength(): int;

    /**
     * Path to send request (based on selected model; without leading slash)
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Path to send request (based on selected model; without leading slash)
     *
     * @param string $value
     * @return ModelInterface
     */
    public function setPath(string $value): ModelInterface;

    /**
     * @param RequestInterface $request
     * @param OptionsInterface $options
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request, OptionsInterface $options): ResponseInterface;

    /**
     * Add request to queue.
     * Return id of queue item.
     *
     * @param RequestInterface $request
     * @param OptionsInterface $options
     * @return int
     */
    public function addToQueue(RequestInterface $request, OptionsInterface $options): int;
}
