<?php

namespace MageWorx\OpenAI\Api;

interface RequestInterface
{
    public const ROLE_SYSTEM = 'system';
    public const ROLE_USER = 'user';

    public function getRole(): string;
    public function setRole(string $role): RequestInterface;

    /**
     * Message or similar data to request
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Message or similar data to request
     *
     * @param string $content
     * @return RequestInterface
     */
    public function setContent(string $content): RequestInterface;

    /**
     * Array of context strings
     *
     * @return string[]
     */
    public function getContext(): array;

    /**
     * Array of context strings
     *
     * @param array $data
     * @return RequestInterface
     */
    public function setContext(array $data): RequestInterface;

    /**
     * Path to make a request; Based on selected model;
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Path to make a request; Based on selected model;
     *
     * @param string $path
     * @return RequestInterface
     */
    public function setPath(string $path): RequestInterface;

    /**
     * @return string|null
     */
    public function getCallback(): ?string;

    /**
     * @param string|null $callback
     * @return RequestInterface
     */
    public function setCallback(?string $callback): RequestInterface;
}
