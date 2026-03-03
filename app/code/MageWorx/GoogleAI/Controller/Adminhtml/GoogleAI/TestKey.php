<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\GoogleAI\Controller\Adminhtml\GoogleAI;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use MageWorx\GoogleAI\Model\MessengerGemini;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\GoogleAI\Helper\Data as Helper;

/**
 * Controller for testing the GoogleAI api key in the admin panel.
 */
class TestKey extends Action implements HttpPostActionInterface
{
    protected MessengerGemini $messenger;
    protected OptionsInterfaceFactory $optionsFactory;
    protected Helper $helper;

    public function __construct(
        Context $context,
        MessengerGemini $messenger,
        OptionsInterfaceFactory $optionsFactory,
        Helper $helper
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
                $key = $this->helper->getGoogleApiKey();
            }

            $isKeyValid = $this->checkGoogleAIKey($key);
        } else {
            $isKeyValid = false;
        }

        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData(['is_key_valid' => $isKeyValid]);

        return $result;
    }

    /**
     * Send request to GoogleAI API to check if the key is valid.
     *
     * @param string $key
     * @return bool
     */
    protected function checkGoogleAIKey(string $key): bool
    {
        $options = $this->optionsFactory->create();
        $options->setPath("models");
        $options->setHttpMethod(OptionsInterface::HTTP_METHOD_GET);
        $options->setCustomKey($key);
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
