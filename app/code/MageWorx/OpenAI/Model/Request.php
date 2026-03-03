<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model;

use MageWorx\OpenAI\Api\RequestInterface;

class Request implements RequestInterface
{
    protected string $role    = 'system';
    protected string $content = '';
    /** @var string[] */
    protected array   $context  = [];
    protected string  $path;
    protected ?string $callback = null;

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): RequestInterface
    {
        $this->role = $role;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): RequestInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Array of context strings
     *
     * @return string[]
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Array of context strings
     *
     * @param array $data
     * @return RequestInterface
     */
    public function setContext(array $data): RequestInterface
    {
        $this->context = $data;

        return $this;
    }

    /**
     * Path to make a request; Based on selected model;
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Path to make a request; Based on selected model;
     *
     * @param string $path
     * @return RequestInterface
     */
    public function setPath(string $path): RequestInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCallback(): ?string
    {
        return $this->callback;
    }

    /**
     * @inheritDoc
     */
    public function setCallback(?string $callback): RequestInterface
    {
        $this->callback = $callback;

        return $this;
    }
}
