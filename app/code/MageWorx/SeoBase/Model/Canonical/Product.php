<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Canonical;

use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\CrossDomainFactory as CrossDomainFactory;
use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\AssociatedFactory as AssociatedFactory;
use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\SimpleFactory as SimpleFactory;
use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;
use MageWorx\SeoBase\Model\Source\CanonicalType;

/**
 * SEO Base product canonical URL model
 */
class Product extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\CrossDomainFactory
     */
    protected $crossDomainFactory;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\AssociatedFactory
     */
    protected $associatedFactory;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\SimpleFactory
     */
    protected $simpleFactory;

    /**
     * @var
     */
    protected $entity;

    /**
     * @var string[]|null
     */
    protected $canonicalUrls;

    /**
     * @var int[]
     */
    protected $canonicalStoreIds = [];

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Product constructor.
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     * @param \Magento\Framework\Registry $registry
     * @param CrossDomainFactory $crossDomainFactory
     * @param AssociatedFactory $associatedFactory
     * @param SimpleFactory $simpleFactory
     * @param string $fullActionName
     * @param \Magento\Framework\UrlInterface|null $url
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        CustomCanonicalRepositoryInterface $customCanonicalRepository,
        \Magento\Framework\Registry $registry,
        CrossDomainFactory $crossDomainFactory,
        AssociatedFactory $associatedFactory,
        SimpleFactory $simpleFactory,
        $fullActionName = 'catalog_product_view',
        \Magento\Framework\UrlInterface $url = null
    ) {
        $this->registry           = $registry;
        $this->crossDomainFactory = $crossDomainFactory;
        $this->associatedFactory  = $associatedFactory;
        $this->simpleFactory      = $simpleFactory;
        $this->url = $url ?: $this->getUrlModel();
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $customCanonicalRepository, $fullActionName);
    }

    /**
     * Retrieve product canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }

        $product = $this->getEntity();

        if (!$product) {
            $product = $this->registry->registry('current_product');
            if (!$product) {
                return null;
            }
        }

        $productId = (int)$product->getId();

        if (isset($this->canonicalUrls[$productId])) {
            return $this->canonicalUrls[$productId];
        }

        $this->canonicalUrls[$productId] = $this->getCustomCanonicalUrl($product);

        if ($this->canonicalUrls[$productId]) {
            return $this->canonicalUrls[$productId];
        }

        $crossDomainStoreByProduct = $this->getCrossDomainStoreId((int)$product->getCrossDomainStore());
        $crossDomainStoreByConfig  = $this->getCrossDomainStoreId($this->helperData->getCrossDomainStore());

        $crossDomainUrlByProduct = $product->getCrossDomainUrl();
        $crossDomainUrlByConfig  = $this->helperData->getCrossDomainUrl();

        if ($crossDomainStoreByProduct) {
            /** @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\CrossDomain $crossDomainProductModel */
            $crossDomainProductModel = $this->crossDomainFactory->create();
            $crossDomainDataObject   = $crossDomainProductModel->getCrossDomainData(
                $product->getId(),
                $crossDomainStoreByProduct,
                null
            );
            if (is_object($crossDomainDataObject)) {
                $canonicalUrl = $crossDomainDataObject->getUrl();

                if ($this->isGraphQl($this->url)) {
                    $canonicalUrl = $this->getUrlPathFromUrl($canonicalUrl, $crossDomainStoreByProduct);
                }

                if ($canonicalUrl) {
                    $this->canonicalStoreIds[$product->getId()] = $crossDomainStoreByProduct;
                }
            }
        } elseif ($crossDomainUrlByProduct) {
            $canonicalUrl = $this->getCrossDomainUrlByCustomUrl(
                $crossDomainUrlByProduct,
                (string)$this->getProductUrl($product)
            );
        } elseif ($crossDomainStoreByConfig) {
            $crossDomainConfigModel = $this->crossDomainFactory->create();
            $crossDomainDataObject  = $crossDomainConfigModel->getCrossDomainData(
                $product->getId(),
                $crossDomainStoreByConfig,
                null
            );
            if (is_object($crossDomainDataObject)) {
                $canonicalUrl = $crossDomainDataObject->getUrl();
                if ($this->isGraphQl($this->url)) {
                    $canonicalUrl = $this->getUrlPathFromUrl($canonicalUrl, $crossDomainStoreByConfig);
                }

                if ($canonicalUrl) {
                    $this->canonicalStoreIds[$product->getId()] = $crossDomainStoreByConfig;
                }
            }
        } elseif ($crossDomainUrlByConfig) {
            $canonicalUrl = $this->getCrossDomainUrlByCustomUrl(
                $crossDomainUrlByConfig,
                (string)$this->getProductUrl($product)
            );
        }

        $associatedProductTypes = $this->helperData->getAssociatedProductTypesAsArray();
        if (empty($canonicalUrl) && $associatedProductTypes) {
            /** @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\Associated $associatedProductModel */
            $associatedProductModel      = $this->associatedFactory->create();
            $associatedProductDataObject = $associatedProductModel->getAssociatedData(
                $product->getId(),
                $associatedProductTypes,
                $product->getStoreId()
            );
            if (is_object($associatedProductDataObject)) {
                $canonicalUrl = $associatedProductDataObject->getUrl();
            }
        }

        if (empty($canonicalUrl)) {
            $canonicalUrl = $this->getProductUrl($product, false);

            if ($this->isGraphQl($this->url)) {
                $canonicalUrl = $this->getUrlPathFromUrl($canonicalUrl);
            }
        }

        $this->canonicalUrls[$productId] = $canonicalUrl ? $this->renderUrl($canonicalUrl) : '';

        return $this->canonicalUrls[$productId];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomCanonicalUrl(\Magento\Catalog\Model\Product $product)
    {
        $customCanonical = $this->customCanonicalRepository->getBySourceEntityData(
            Rewrite::ENTITY_TYPE_PRODUCT,
            $product->getId(),
            $product->getStoreId(),
            false
        );

        if ($customCanonical) {
            $canonicalStoreId = null;
            $canonicalUrl     = $this->customCanonicalRepository->getCustomCanonicalUrl(
                $customCanonical,
                $product->getStoreId(),
                $canonicalStoreId,
                $this->isGraphQl($this->url)
            );

            if ($canonicalUrl) {
                $this->canonicalStoreIds[$product->getId()] = $canonicalStoreId;

                return $this->renderUrl($canonicalUrl);
            }
        }

        return null;
    }

    /**
     * @param int $entityId
     * @return null
     */
    public function getCanonicalStoreId($entityId)
    {
        if (array_key_exists($entityId, $this->canonicalStoreIds)) {
            return $this->canonicalStoreIds[$entityId];
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $forceRootType
     * @return mixed
     */
    protected function getProductUrl(\Magento\Catalog\Model\Product $product, bool $forceRootType = true)
    {
        $urlType = $this->helperData->getProductCanonicalUrlType($product->getStoreId());

        if ($forceRootType || $urlType == CanonicalType::URL_TYPE_NO_CATEGORIES) {
            return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
        }

        /** @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\Simple $simpleDataObject */
        $simpleCanonicalModel = $this->simpleFactory->create();
        $simpleDataObject     = $simpleCanonicalModel->getCanonicalData(
            $product->getStoreId(),
            $product->getId()
        );
        if (is_object($simpleDataObject)) {
            return $simpleDataObject->getUrl();
        }

        return null;
    }

    /**
     *  Retrieve cross domain store ID
     *
     * @param int $storeId
     * @return int|false
     */
    protected function getCrossDomainStoreId(int $storeId)
    {
        if (!$storeId) {
            return false;
        }
        if (!$this->helperStoreUrl->isActiveStore($storeId)) {
            return false;
        }
        if ($this->helperStoreUrl->getCurrentStoreId() == $storeId) {
            return false;
        }

        return $storeId;
    }

    /**
     * Retrieve cross domain URL
     *
     * @param string $crossDomainBaseUrl
     * @param string $productUrl
     * @return string
     */
    protected function getCrossDomainUrlByCustomUrl(string $crossDomainBaseUrl, string $productUrl): string
    {
        $crossDomainBaseUrlTrim = rtrim(trim($crossDomainBaseUrl), '/') . '/';
        $storeBaseUrl           = $this->helperStoreUrl->getStoreBaseUrl();

        return str_replace($storeBaseUrl, $crossDomainBaseUrlTrim, $productUrl);
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function getUrlModel()
    {
        if (!$this->url) {
            $this->url = ObjectManager::getInstance()->get(\Magento\Framework\UrlInterface::class);
        }

        return $this->url;
    }
}
