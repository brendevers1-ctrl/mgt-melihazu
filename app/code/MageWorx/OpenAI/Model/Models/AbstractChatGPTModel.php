<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Models;

use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\RequestInterface;
use MageWorx\OpenAI\Api\ResponseInterface;

/**
 * Class AbstractChatGPTModel
 * Abstract class for chat GPT models.
 * Covers request data generation.
 */
abstract class AbstractChatGPTModel extends AbstractModel
{
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
     *
     * @param RequestInterface $request
     * @param OptionsInterface $options
     * @return array
     */
    protected function getRawDataForRequest(RequestInterface $request, OptionsInterface $options): array
    {
        $data = [
            'model'       => $this->getType(),
            'n'           => $options->getNumberOfResultOptions(),
            'messages'    => $this->getMessagesDataFromRequest($request),
            'stream'      => false,
            'temperature' => $options->getTemperature()
        ];

        if ($options->getMaxTokens() && $this->useMaxTokens()) {
            $data['max_tokens'] = $options->getMaxTokens();
        }

        return $data;
    }

    /**
     * Get message and context as array
     *
     * @param RequestInterface $request
     * @return array
     */
    protected function getMessagesDataFromRequest(RequestInterface $request): array
    {
        $messages = [];

        $context = $request->getContext();
        foreach ($context as $contextItem) {
            $messages[] = [
                'role'    => RequestInterface::ROLE_SYSTEM,
                'content' => $this->prepareMessageContent($contextItem)
            ];
        }

        $messages[] = [
            'role'    => RequestInterface::ROLE_USER,
            'content' => $this->prepareMessageContent($request->getContent()),
        ];

        return $messages;
    }
}
