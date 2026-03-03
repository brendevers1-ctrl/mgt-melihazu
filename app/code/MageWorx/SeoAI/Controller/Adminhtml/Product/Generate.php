<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoAI\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoAI\Helper\Config;
use MageWorx\SeoAI\Model\GeneratorFactory;
use Psr\Log\LoggerInterface;

class Generate extends Action
{
    protected GeneratorFactory $generatorFactory;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Json
     */
    protected Json $jsonSerializer;

    protected AppEmulation          $appEmulation;
    protected StoreManagerInterface $storeManager;
    protected Config                $config;

    /**
     * @param Context $context
     * @param GeneratorFactory $generatorFactory
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context               $context,
        GeneratorFactory      $generatorFactory,
        Json                  $jsonSerializer,
        AppEmulation          $appEmulation,
        StoreManagerInterface $storeManager,
        Config                $config,
        LoggerInterface       $logger
    ) {
        $this->generatorFactory = $generatorFactory;
        $this->jsonSerializer   = $jsonSerializer;
        $this->appEmulation     = $appEmulation;
        $this->storeManager     = $storeManager;
        $this->config           = $config;
        $this->logger           = $logger;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultObject */
        $resultObject = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        // Used to detect corresponding model
        $messageType = $this->getRequest()->getParam('message_type');
        try {
            $messageModel = $this->generatorFactory->create($messageType);
        } catch (LocalizedException $exception) {
            $resultObject->setJsonData(
                $this->jsonSerializer->serialize(
                    ['error' => true, 'message' => $exception->getLogMessage()]
                )
            );

            return $resultObject;
        }

        $defaultModel = $this->config->getValue('mageworx_seo/mageworx_seoai/openai_model') ?? 'chat_gpt_3d5';

        $productId          = (int)$this->getRequest()->getParam('product_id');
        $storeId            = (int)$this->getRequest()->getParam('store_id') ?? 0;
        $openAIModel        = $this->getRequest()->getParam('openai_model') ?? $defaultModel;
        $selectedAttributes = $this->getRequest()->getParam('product_attributes') ?? [];
        $temperature        = (float)$this->getRequest()->getParam('temperature') ?: 1;
        $numberOfResults    = (int)$this->getRequest()->getParam('number_of_results') ?: 1;

        // Emulate store to make result in selected store language (including attribute names)
        $this->appEmulation->startEnvironmentEmulation($storeId);
        $result = $messageModel->execute(
            $productId,
            $storeId,
            $openAIModel,
            $selectedAttributes,
            $temperature,
            $numberOfResults
        );
        $this->appEmulation->stopEnvironmentEmulation();

        $this->logger->info('RESULT: ');
        $this->logger->info($result);

        $resultObject->setJsonData($result);

        return $resultObject;
    }
}
