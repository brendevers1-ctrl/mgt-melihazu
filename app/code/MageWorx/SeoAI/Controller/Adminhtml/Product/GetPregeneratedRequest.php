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
use MageWorx\SeoAI\Model\GeneratorFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Store\Model\StoreManagerInterface;

class GetPregeneratedRequest extends Action
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
        LoggerInterface       $logger
    ) {
        $this->generatorFactory = $generatorFactory;
        $this->jsonSerializer   = $jsonSerializer;
        $this->appEmulation     = $appEmulation;
        $this->storeManager     = $storeManager;
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

        $productId          = (int)$this->getRequest()->getParam('product_id');
        $storeId            = (int)$this->getRequest()->getParam('store_id') ?? 0;
        $selectedAttributes = $this->getRequest()->getParam('product_attributes') ?? [];

        // Emulate store to make result in selected store language (including attribute names)
        $this->appEmulation->startEnvironmentEmulation($storeId);
        $content = $messageModel->generateRequestMessage($productId, $storeId, $selectedAttributes);
        $context = $messageModel->generateRequestContext($storeId);
        $this->appEmulation->stopEnvironmentEmulation();

        $result = json_encode(
            [
                'content' => $content,
                'context' => $context
            ]
        );

        $resultObject->setJsonData($result);

        return $resultObject;
    }
}
