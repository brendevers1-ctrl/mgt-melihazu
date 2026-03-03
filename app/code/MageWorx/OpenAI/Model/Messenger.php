<?php

namespace MageWorx\OpenAI\Model;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\OpenAI\Api\MessengerInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Api\ResponseInterfaceFactory;
use MageWorx\OpenAI\Helper\Data as Helper;

class Messenger implements MessengerInterface
{
    const API_URL = 'https://api.openai.com/';

    protected Helper                   $helper;
    protected OptionsInterfaceFactory  $optionsFactory;
    protected RequestInterfaceFactory  $requestFactory;
    protected ResponseInterfaceFactory $responseFactory;

    public function __construct(
        Helper                   $helper,
        OptionsInterfaceFactory  $optionsFactory,
        RequestInterfaceFactory  $requestFactory,
        ResponseInterfaceFactory $responseFactory
    ) {
        $this->helper          = $helper;
        $this->optionsFactory  = $optionsFactory;
        $this->requestFactory  = $requestFactory;
        $this->responseFactory = $responseFactory;
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

        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
            $responseObject->setIsError(false);
        } elseif (isset($result['data'])) {
            $content = is_string($result['data']) ? $result['data'] : json_encode($result['data']);
            $responseObject->setIsError(false);
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

        $curlOptions = [
            CURLOPT_URL            => static::API_URL . $options->getPath(),
            CURLOPT_HTTPHEADER     => $options->getHeaders(),
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
}
