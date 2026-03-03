<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\OpenAI;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use MageWorx\OpenAI\Api\MessengerInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Helper\Data as Helper;

/**
 * Controller for testing the OpenAI api key in the admin panel.
 */
class TestKey extends Action implements HttpPostActionInterface
{
    protected MessengerInterface      $messenger;
    protected OptionsInterfaceFactory $optionsFactory;
    protected Helper                  $helper;

    public function __construct(
        Context                 $context,
        MessengerInterface      $messenger,
        OptionsInterfaceFactory $optionsFactory,
        Helper                  $helper
    ) {
        parent::__construct($context);
        $this->messenger      = $messenger;
        $this->optionsFactory = $optionsFactory;
        $this->helper         = $helper;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $key = $this->getRequest()->getParam('sk');

        if (!empty($key)) {
            if ($this->isHashedKey($key)) {
                $key = $this->helper->getApiKey();
            }

            $isKeyValid = $this->checkOpenAIKey($key);
        } else {
            $isKeyValid = false;
        }

        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData(['is_key_valid' => $isKeyValid]);

        return $result;
    }

    /**
     * Send request to OpenAI API to check if the key is valid.
     *
     * @param string $key
     * @return bool
     */
    protected function checkOpenAIKey(string $key): bool
    {
        $options = $this->optionsFactory->create();
        $options->addHeaders(['Authorization' => 'Authorization: Bearer ' . $key]);
        $options->setPath('v1/models');
        $options->setHttpMethod(OptionsInterface::HTTP_METHOD_GET);

        $response = $this->messenger->send([], $options);

        return !$response->getIsError();
    }

    /**
     * Determines if a given key is hashed.
     *
     * @param string $key The key to check.
     * @return bool Returns true if the key is hashed, false otherwise.
     */
    protected function isHashedKey(string $key): bool
    {
        return str_starts_with($key, '*') && str_ends_with($key, '*');
    }
}
