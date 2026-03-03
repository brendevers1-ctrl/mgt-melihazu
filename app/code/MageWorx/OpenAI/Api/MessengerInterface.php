<?php

namespace MageWorx\OpenAI\Api;

interface MessengerInterface
{
    /**
     * Send request, get response
     *
     * @param array $data
     * @param OptionsInterface $options
     * @return ResponseInterface
     */
    public function send(array $data, OptionsInterface $options): ResponseInterface;
}
