<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\GoogleAI\Model\Models\Gemini;

use MageWorx\OpenAI\Api\ModelInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\RequestInterface;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Model\Models\AbstractModel as AbstractAIModel;

abstract class AbstractGeminiModel extends AbstractAIModel implements ModelInterface
{
    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request, OptionsInterface $options): ResponseInterface
    {
        $options->setModel($this->getType());
        $options->setPath($request->getPath());

        $data = $this->getRawDataForRequest($request, $options);

        $response = $this->messenger->send($data, $options);

        return $response;
    }

    /**
     * Generate request data as array using request and selected options
     * @link https://ai.google.dev/api/rest/v1beta/models/generateContent
     *
     * @param RequestInterface $request
     * @param OptionsInterface $options
     * @return array
     */
    protected function getRawDataForRequest(RequestInterface $request, OptionsInterface $options): array
    {
        $data = [
            'contents'         => $this->getMessagesDataFromRequest($request),
            'generationConfig' => [
                'candidateCount'  => $options->getNumberOfResultOptions(),
                'temperature'     => $options->getTemperature(),
                'maxOutputTokens' => $options->getMaxTokens()
            ]
        ];

        return $data;
    }

    /**
     * Get message and context as array
     * {
     * "contents": {
     * "role": "user",
     * "parts": [
     * {
     * "text": "What\\'s a good name for a flower shop that specializes in selling bouquets of dried flowers?"
     * }
     * ]
     * }
     * }
     *
     * @param RequestInterface $request
     * @return array
     */
    protected function getMessagesDataFromRequest(RequestInterface $request): array
    {
        $parts = [];
        // Template for request body
        $result = [
            'role'  => RequestInterface::ROLE_USER,
            'parts' => &$parts
        ];

        // Context
        $context = $request->getContext();
        foreach ($context as $contextItem) {
            $parts[] = [
                'text' => $this->prepareMessageContent($contextItem)
            ];
        }

        // Main content as last message
        $parts[] = [
            'text' => $this->prepareMessageContent($request->getContent()),
        ];

        return $result;
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
