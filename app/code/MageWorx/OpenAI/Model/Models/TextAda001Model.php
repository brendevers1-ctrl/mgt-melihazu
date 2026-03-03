<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Models;

use MageWorx\OpenAI\Api\MessengerInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\RequestInterface;
use MageWorx\OpenAI\Api\ResponseInterface;

class TextAda001Model extends AbstractModel
{
    /** @var string Model type */
    protected string $type = 'text-ada-001';
    protected string $path = 'v1/completions';
    protected int $maxContextLength = 2049;

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
            'model' => $this->getType(),
            'n' => $options->getNumberOfResultOptions(),
            'temperature' => $options->getTemperature(),
            'prompt' => $request->getContent(),
            'max_tokens' => 600
        ];

        if ($options->getMaxTokens() && $this->useMaxTokens()) {
            $data['max_tokens'] = $options->getMaxTokens();
        }

        return $data;
    }
}
