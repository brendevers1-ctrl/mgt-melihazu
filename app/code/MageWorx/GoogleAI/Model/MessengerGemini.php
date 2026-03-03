<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\GoogleAI\Model;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\GoogleAI\Helper\Data as GoogleAIHelper;
use MageWorx\OpenAI\Api\MessengerInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Api\ResponseInterfaceFactory;
use MageWorx\OpenAI\Helper\Data as Helper;

class MessengerGemini implements MessengerInterface
{
    const API_URL = 'https://generativelanguage.googleapis.com/v1beta/';

    protected Helper $helper;
    protected OptionsInterfaceFactory $optionsFactory;
    protected RequestInterfaceFactory $requestFactory;
    protected ResponseInterfaceFactory $responseFactory;
    protected GoogleAIHelper $googleAIHelper;

    public function __construct(
        Helper $helper,
        OptionsInterfaceFactory $optionsFactory,
        RequestInterfaceFactory $requestFactory,
        ResponseInterfaceFactory $responseFactory,
        GoogleAIHelper $googleAIHelper
    ) {
        $this->helper          = $helper;
        $this->optionsFactory  = $optionsFactory;
        $this->requestFactory  = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->googleAIHelper  = $googleAIHelper;
    }

    /**
     * @param array $data
     * @param OptionsInterface $options
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function send(array $data, OptionsInterface $options): ResponseInterface
    {
        $response = $this->sendRequest($data, $options);

        // Handle the response
        $result = json_decode($response, true);
        /** @var ResponseInterface $responseObject */
        $responseObject = $this->responseFactory->create();

        /**
         * {
         * "candidates": [
         * {
         * "content": {
         * "parts": [
         * {
         * "text": "Here are some good names for a flower shop that specializes in selling bouquets of dried flowers:\n\n**Elegant & Classic:**\n\n* **Everlasting Bloom**\n* **The Dried Flower Co.**\n* **The Bloom Bar**\n* **Whispers of Bloom**\n* **The Petal Pantry**\n* **Dried & Delicate**\n\n**Unique & Playful:**\n\n* **The Dusty Bouquet**\n* **Bloom & Preserve**\n* **Forever in Bloom**\n* **The Botanical Archive**\n* **Dried Flower Dreams**\n* **The Flower Alchemist**\n\n**Modern & Minimalist:**\n\n* **Bloom & Co.**\n* **The Bloom Studio**\n* **Dried Blooms**\n* **The Flower House**\n* **Simply Bloom**\n* **The Flower Lab**\n\n**Location-Specific:**\n\n* **[Your City] Dried Flowers**\n* **[Your Street] Blooms**\n* **The [Neighborhood] Flower Shop**\n\n**Tips for Choosing a Name:**\n\n* **Keep it short and memorable.**\n* **Reflect your brand's personality.**\n* **Check if the name is available as a domain and social media handle.**\n* **Consider your target audience.**\n\nUltimately, the best name for your flower shop will be one that you love and that accurately reflects your brand. Good luck! \n"
         * }
         * ],
         * "role": "model"
         * },
         * "finishReason": "STOP",
         * "index": 0,
         * "safetyRatings": [
         * {
         * "category": "HARM_CATEGORY_SEXUALLY_EXPLICIT",
         * "probability": "NEGLIGIBLE"
         * },
         * {
         * "category": "HARM_CATEGORY_HATE_SPEECH",
         * "probability": "NEGLIGIBLE"
         * },
         * {
         * "category": "HARM_CATEGORY_HARASSMENT",
         * "probability": "NEGLIGIBLE"
         * },
         * {
         * "category": "HARM_CATEGORY_DANGEROUS_CONTENT",
         * "probability": "NEGLIGIBLE"
         * }
         * ]
         * }
         * ],
         * "usageMetadata": {
         * "promptTokenCount": 20,
         * "candidatesTokenCount": 278,
         * "totalTokenCount": 298
         * }
         * }
         */
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $result['candidates'][0]['content']['parts'][0]['text'];
            $responseObject->setIsError(false);
            $result = $this->prepareResponse($result);
        } elseif (isset($result['models'])) {
            $responseObject->setIsError(false);
            $result  = [];
            $content = '';
        } elseif (isset($result['error']['message'])) {
            $errorStatus  = $result['error']['status'] ?? 'UNKNOWN';
            $errorMessage = $result['error']['message'] ?? 'Unknown error';
            $errorCode    = $result['error']['code'] ?? 'UNKNOWN';
            $content      = 'Error [' . $errorStatus . '] : ' . $errorMessage . ' (' . $errorCode . ')';
            $responseObject->setIsError(true);
        } else {
            $content = 'Error: ' . $response;
            $responseObject->setIsError(true);
        }

        $responseObject->setChatResponse($result);
        $responseObject->setContent($content);

        return $responseObject;
    }

    /**
     * Send request and get raw data (json string)
     *
     * @param array $data
     * @param OptionsInterface $options
     * @return string
     * @throws LocalizedException
     */
    protected function sendRequest(array $data, OptionsInterface $options): string
    {
        $jsonData = json_encode($data);
        $ch       = curl_init();

        $headers[] = 'Content-Type: application/json';
        $apiKey    = $options->getCustomKey() ?? $this->googleAIHelper->getGoogleApiKey();

        $curlOptions = [
            CURLOPT_URL            => static::API_URL . $options->getPath() . '?key=' . $apiKey,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $options->getHttpMethod(),
        ];

        if ($options->getHttpMethod() === 'POST') {
            $curlOptions[CURLOPT_POSTFIELDS] = $jsonData;
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new LocalizedException(__('Curl Error: %1', curl_error($ch)));
        }

        curl_close($ch);

        return $response;
    }

    /**
     * @TODO: Create and use general converter class for all models.
     * method: convert(string $from, string $to, array|ResponseInterface $response) where from - model code from which
     *     we're converting, to - model code to which we converting, response - response from API
     * @param array $geminiResponse
     * @return array|array[]
     */
    protected function prepareResponse(array $geminiResponse): array
    {
        $chatGPTResponse = [
            'choices' => [],
        ];

        foreach ($geminiResponse['candidates'] as $candidate) {
            if (isset($candidate['content']['parts'][0]['text'])) {
                $chatGPTResponse['choices'][] = [
                    'message' => [
                        'content' => $candidate['content']['parts'][0]['text']
                    ]
                ];
            }
        }

        return $chatGPTResponse;
    }
}
