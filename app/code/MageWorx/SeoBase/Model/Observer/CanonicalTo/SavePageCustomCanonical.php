<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer\CanonicalTo;

use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use MageWorx\SeoBase\Api\Data\CustomCanonicalInterface;
use MageWorx\SeoBase\Model\Source\CustomCanonical\CanonicalUrlType;
use MageWorx\SeoBase\Model\CustomCanonical as CustomCanonicalModel;
use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;
use MageWorx\SeoBase\Helper\CustomCanonical as HelperCustomCanonical;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\RequestInterface;

class SavePageCustomCanonical implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var CustomCanonicalRepositoryInterface
     */
    private $customCanonicalRepository;

    /**
     * @var HelperCustomCanonical
     */
    private $helperCustomCanonical;

    /**
     * SaveProductCustomCanonical constructor.
     *
     * @param RequestInterface $request
     * @param EventManager $eventManager
     * @param MessageManager $messageManager
     * @param LoggerInterface $logger
     * @param HelperCustomCanonical $helperCustomCanonical
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     */
    public function __construct(
        RequestInterface $request,
        EventManager $eventManager,
        MessageManager $messageManager,
        LoggerInterface $logger,
        HelperCustomCanonical $helperCustomCanonical,
        CustomCanonicalRepositoryInterface $customCanonicalRepository
    ) {
        $this->request                   = $request;
        $this->eventManager              = $eventManager;
        $this->messageManager            = $messageManager;
        $this->logger                    = $logger;
        $this->helperCustomCanonical     = $helperCustomCanonical;
        $this->customCanonicalRepository = $customCanonicalRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->getEvent()->getObject();

        $entityData        = $this->request->getPostValue();
        $customCanonicalId = !empty($entityData['custom_canonical_id']) ? $entityData['custom_canonical_id'] : null;

        if (empty($entityData['canonical_url_type'])) {
            $canonicalUrlType = CanonicalUrlType::TYPE_DEFAULT;
        } else {
            $canonicalUrlType = $entityData['canonical_url_type'];
        }

        if ($canonicalUrlType == CanonicalUrlType::TYPE_DEFAULT && $customCanonicalId) {

            $this->deleteCustomCanonical($customCanonicalId);

        } elseif ($canonicalUrlType == CanonicalUrlType::TYPE_CUSTOM
            && !empty($entityData['mageworx_seobase_cms_page_canonical'])
        ) {
            $this->saveCustomCanonical($customCanonicalId, $entityData, $entity);
        }
    }

    /**
     * Delete Custom Canonical
     *
     * @param int $customCanonicalId
     * @return void
     */
    private function deleteCustomCanonical($customCanonicalId)
    {
        try {
            $this->customCanonicalRepository->deleteById($customCanonicalId);
            $this->messageManager->addSuccessMessage(__('The Custom Canonical has been deleted.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the Custom Canonical right now. Please review the log and try again.')
            );
            $this->logger->critical($e);
        }
    }

    /**
     * Save Custom Canonical
     *
     * @param int|null $customCanonicalId
     * @param array $entityData
     * @param $entity
     * @return void
     */
    private function saveCustomCanonical($customCanonicalId, $entityData, $entity)
    {
        $canonicalToData = $entityData['mageworx_seobase_cms_page_canonical'];

        if (empty($entityData['current_store_id'])) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        } else {
            $storeId = $entityData['current_store_id'];
        }

        $data = [
            CustomCanonicalInterface::SOURCE_ENTITY_TYPE => Rewrite::ENTITY_TYPE_CMS_PAGE,
            CustomCanonicalInterface::SOURCE_ENTITY_ID   => $entity->getId(),
            CustomCanonicalInterface::SOURCE_STORE_ID    => $storeId
        ];

        try {
            $data = array_merge($data, $this->prepareData($canonicalToData));

            if ($customCanonicalId) {
                /** @var CustomCanonicalModel $customCanonical */
                $customCanonical = $this->customCanonicalRepository->getById($customCanonicalId);
                $isEdit          = true;
            } else {
                $customCanonical = $this->customCanonicalRepository->getEmptyEntity();
                $isEdit          = false;
            }

            $customCanonical->setData($data);

            if ($isEdit) {
                $customCanonical->setId($customCanonicalId);
            }

            if ($this->helperCustomCanonical->isRecursiveCustomCanonical($customCanonical)) {
                throw new LocalizedException(
                    __('It is impossible to save Custom Canonical: Source and Target entities can\'t be identical!')
                );
            }

            $this->eventManager->dispatch(
                CustomCanonicalModel::CURRENT_CUSTOM_CANONICAL . '_prepare_save',
                [
                    'custom_canonical' => $customCanonical,
                    'request'          => $this->request
                ]
            );

            $this->customCanonicalRepository->save($customCanonical);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the Custom Canonical.')
            );
            $this->logger->critical($e);
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        $targetEntityType = $data[CustomCanonicalInterface::TARGET_ENTITY_TYPE];

        if ($targetEntityType != Rewrite::ENTITY_TYPE_CUSTOM) {
            $data[CustomCanonicalInterface::TARGET_ENTITY_ID] =
                $data[$targetEntityType . '_' . CustomCanonicalInterface::TARGET_ENTITY_ID];
        }

        return $data;
    }
}
