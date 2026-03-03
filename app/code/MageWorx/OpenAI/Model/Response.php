<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model;

use MageWorx\OpenAI\Api\ResponseInterface;

class Response implements ResponseInterface
{
    protected string $content     = '';
    protected array  $rawResponse = [];
    protected bool   $isError     = false;

    /**
     * @inheritDoc
     */
    public function setContent(string $content): ResponseInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function setChatResponse(array $response): ResponseInterface
    {
        $this->rawResponse = $response;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChatResponse(): array
    {
        return $this->rawResponse;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        // This is where you control what data will be json encoded
        return [
            'content'     => $this->content,
            'rawResponse' => $this->rawResponse,
            'isError'     => $this->isError
        ];
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $data): ResponseInterface
    {
        $this->content     = $data['content'];
        $this->rawResponse = $data['rawResponse'];
        $this->isError     = $data['isError'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIsError(bool $isError): ResponseInterface
    {
        $this->isError = $isError;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIsError(): bool
    {
        return $this->isError;
    }
}
