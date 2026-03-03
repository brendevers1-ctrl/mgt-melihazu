<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\SeoAI\Model\GeneratorFactory;
use Psr\Log\LoggerInterface;

class AddCategoriesToQueue extends Action
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

    protected AppEmulation              $appEmulation;
    protected StoreManagerInterface     $storeManager;
    protected Filter                    $filter;
    protected CategoryCollectionFactory $categoryCollectionFactory;

    public function __construct(
        Context                   $context,
        GeneratorFactory          $generatorFactory,
        Filter                    $filter,
        CategoryCollectionFactory $categoryCollectionFactory,
        Json                      $jsonSerializer,
        AppEmulation              $appEmulation,
        StoreManagerInterface     $storeManager,
        LoggerInterface           $logger
    ) {
        $this->generatorFactory          = $generatorFactory;
        $this->filter                    = $filter;
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        $this->jsonSerializer = $jsonSerializer;
        $this->appEmulation   = $appEmulation;
        $this->storeManager   = $storeManager;
        $this->logger         = $logger;

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

        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->filter->getCollection(
            $this->categoryCollectionFactory->create()
        );

        // Prepare basic params from request
        $categoryIds        = $categoryCollection->getAllIds() ?: $this->getRequest()->getParam('category_ids');
        $openAIModel        = $this->getRequest()->getParam('openai_model') ?? 'gpt-3.5-turbo';
        $temperature        = (float)$this->getRequest()->getParam('temperature') ?: 1;
        $selectedAttributes = $this->getRequest()->getParam('category_attributes') ?? [];
        $numberOfResults    = 1;

        // Prepare store ids
        $storeIdsFromRequest = $this->getRequest()->getParam('store_id');
        if (!is_array($storeIdsFromRequest)) {
            $storeIds = [$storeIdsFromRequest ? (int)$storeIdsFromRequest : 0];
        } else {
            $storeIds = array_map('intval', $storeIdsFromRequest);
        }

        // Prepare pregenerated request data
        $pregeneratedRequestData = $this->getRequest()->getParam('pregenerated_request_data');
        if (is_array($pregeneratedRequestData)) {
            $content = (string)$pregeneratedRequestData['content'];
            $context = !empty($pregeneratedRequestData['context'])
                ? explode("\n", (string)$pregeneratedRequestData['context'])
                : [];
        } else {
            $content = '';
            $context = [];
        }

        // Trim context to save tokens
        $context = array_map('trim', $context);

        $processCode = 'seo-generate-category-' . $messageType . '_' . time();

        // Add items to queue per store
        $queuedItems = [];
        foreach ($storeIds as $storeId) {
            // Emulate store to make result in selected store language (including attribute names)
            $this->appEmulation->startEnvironmentEmulation($storeId);
            foreach ($categoryIds as $categoryId) {
                $queuedItems[] = $messageModel->addToQueue(
                    (int)$categoryId,
                    (int)$storeId,
                    $openAIModel,
                    $selectedAttributes,
                    $temperature,
                    $numberOfResults,
                    $content,
                    $context,
                    $processCode
                );
            }
            $this->appEmulation->stopEnvironmentEmulation();
        }

        // Prepare response to AJAX request
        $resultObject->setData(
            [
                'error'   => false,
                'message' => __('%1 categories were added to the queue.', count($queuedItems))
            ]
        );

        return $resultObject;
    }
}
