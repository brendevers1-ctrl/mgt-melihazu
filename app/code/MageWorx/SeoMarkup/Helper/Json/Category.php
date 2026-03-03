<?php
/**
 * Copyright © 2019 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Helper\Json;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

class Category
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Category
     */
    protected $helperCategory;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Category
     */
    protected $helperDataProvider;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperProductDataProvider;

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $helperCatalog;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoMarkup';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * Category constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Category $helperCategory
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Category $dataProviderCategory
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     * @param \Magento\Catalog\Helper\Data $helperCatalog
     * @param PriceCurrencyInterface $priceCurrency
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Category $helperCategory,
        \MageWorx\SeoMarkup\Helper\DataProvider\Category $dataProviderCategory,
        \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Page\Config $pageConfig,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct,
        \Magento\Catalog\Helper\Data $helperCatalog,
        PriceCurrencyInterface $priceCurrency,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->registry                  = $registry;
        $this->helperCategory            = $helperCategory;
        $this->helperDataProvider        = $dataProviderCategory;
        $this->helperProductDataProvider = $dataProviderProduct;
        $this->layout                    = $layout;
        $this->urlBuilder                = $urlBuilder;
        $this->pageConfig                = $pageConfig;
        $this->helperProduct             = $helperProduct;
        $this->helperCatalog             = $helperCatalog;
        $this->priceCurrency             = $priceCurrency;
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
    }

    /**
     * @return string
     */
    public function getMarkupHtml()
    {
        $html             = '';
        $categoryJsonData = [];

        if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
            return $html;
        }

        $category = $this->registry->registry('current_category');
        if (!is_object($category)) {
            return false;
        }

        if ($this->isContentMode($category)) {
            return $html;
        }

        if ($this->helperCategory->isUseCategoryRobotsRestriction() && $this->isNoindexPage()) {
            return $html;
        }

        if ($this->helperCategory->isRsEnabled()) {
            $categoryJsonData = $this->getJsonCategoryData($category);
        }

        if ($this->helperCategory->isGaEnabled()) {
            $categoryJsonData = array_merge($categoryJsonData, $this->getGoogleAssistantJsonData());
        }

        $categoryJson = !empty($categoryJsonData) ? json_encode($categoryJsonData) : '';

        if ($categoryJsonData) {
            $html .= '<script type="application/ld+json">' . $categoryJson . '</script>';
        }

        return $html;
    }

    /**
     * Check if category display mode is "Static Block Only"
     * For anchor category Static Block Only mode not allowed
     *
     * @return bool
     */
    protected function isContentMode($category)
    {
        $result = false;
        if ($category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PAGE) {
            $result = true;
            if ($category->getIsAnchor()) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return array|bool
     */
    protected function getJsonCategoryData($category)
    {
        $productCollection = $this->getProductCollection();

        $data = [];

        if ($productCollection) {
            $data['@context']                      = 'http://schema.org';
            $data['@type']                         = 'WebPage';
            $data['url']                           = $this->urlBuilder->getCurrentUrl();
            $data['mainEntity']                    = [];
            $data['mainEntity']['@context']        = 'http://schema.org';
            $data['mainEntity']['@type']           = 'OfferCatalog';
            $data['mainEntity']['name']            = $category->getName();
            $data['mainEntity']['url']             = $this->urlBuilder->getCurrentUrl();
            $data['mainEntity']['numberOfItems']   = count($productCollection->getItems());
            $data['mainEntity']['itemListElement'] = [];

            if ($this->helperCategory->isUseOfferForCategoryProducts()) {
                foreach ($productCollection as $product) {
                    $data['mainEntity']['itemListElement'][] = $this->getProductData($product);
                }
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductData(\Magento\Catalog\Model\Product $product): array
    {
        $this->_product = $product;
        $this->helperProductDataProvider->reset();

        $data                = [];
        $data['@type']       = 'Product';
        $data['name']        = $this->_product->getName();
        $data['description'] = $this->helperProductDataProvider->getDescriptionValue($this->_product);

        if($this->helperCategory->getImageMode() === \MageWorx\SeoMarkup\Model\Source\ImageMode::ALL) {
            $data['image'] = $this->helperProductDataProvider->getProductImagesArray($this->_product);
        } else {
            $data['image'] = $this->helperProductDataProvider
                ->getProductImage( $this->_product,'product_page_image_large')
                ->getImageUrl();
        }

        $offers = $this->getOfferData();
        if (!empty($offers['price']) || !empty($offers[0]['price'])) {
            $data['offers'] = $offers;
        }

        $aggregateRatingData = $this->helperProductDataProvider->getAggregateRatingData($this->_product, false);

        if (!empty($aggregateRatingData)) {
            $aggregateRatingData['@type'] = 'AggregateRating';
            $data['aggregateRating']      = $aggregateRatingData;
        }

        /**
         * Google console error: "Either 'offers', 'review' or 'aggregateRating' should be specified"
         */
        if ($this->helperProduct->isRsEnabledForSpecificProduct() === false
            && empty($data['aggregateRating'])
            && empty($data['offers'])
        ) {
            return [];
        }

        $productIdValue = $this->helperProductDataProvider->getProductIdValue($this->_product);

        if ($productIdValue) {
            $data['productID'] = $productIdValue;
        }

        $color = $this->helperProductDataProvider->getColorValue($this->_product);
        if ($color) {
            $data['color'] = $color;
        }

        $brand = $this->helperProductDataProvider->getBrandValue($this->_product);
        if ($brand) {
            $data['brand'] = $brand;
        }

        $manufacturer = $this->helperProductDataProvider->getManufacturerValue($this->_product);
        if ($manufacturer) {
            $data['manufacturer'] = $manufacturer;
        }

        $model = $this->helperProductDataProvider->getModelValue($this->_product);
        if ($model) {
            $data['model'] = $model;
        }

        $gtin = $this->helperProductDataProvider->getGtinData($this->_product);
        if (!empty($gtin['gtinType']) && !empty($gtin['gtinValue'])) {
            $data[$gtin['gtinType']] = $gtin['gtinValue'];
        }

        $skuValue = $this->helperProductDataProvider->getSkuValue($this->_product);
        if ($skuValue) {
            $data['sku'] = $skuValue;
        }

        $weightValue = $this->helperProductDataProvider->getWeightValue($this->_product);
        if ($weightValue) {
            $data['weight'] = $weightValue;
        }

        $categoryName = $this->helperProductDataProvider->getCategoryValue($this->_product);
        if ($categoryName) {
            $data['category'] = $categoryName;
        }

        $customProperties = $this->helperProduct->getCustomProperties();

        if ($customProperties) {
            foreach ($customProperties as $propertyName => $propertyValue) {
                if (!$propertyName || !$propertyValue) {
                    continue;
                }
                $value = $this->helperProductDataProvider->getCustomPropertyValue($product, $propertyValue);
                if ($value) {
                    $data[$propertyName] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getOfferData(): array
    {
        $data = [];

        $data['@type'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::OFFER;
        $data['price'] = $this->getPrice();

        $data['url']           = $this->_product->getProductUrl();
        $data['priceCurrency'] = $this->helperProductDataProvider->getCurrentCurrencyCode();

        if ($this->helperProductDataProvider->getAvailability($this->_product)) {
            $data['availability'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::IN_STOCK;
        } else {
            $data['availability'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::OUT_OF_STOCK;
        }

        $priceValidUntil = $this->helperProductDataProvider->getPriceValidUntilValue($this->_product);

        if ($priceValidUntil) {
            $data['priceValidUntil'] = $priceValidUntil;
        }

        $shippingDetailsData = $this->helperProductDataProvider->getShippingDetailsData($this->_product);
        if (!empty($shippingDetailsData)) {
            $data['shippingDetails'] = $shippingDetailsData;
        }

        $merchantReturnPolicyData = $this->helperProductDataProvider->getMerchantReturnPolicyData($this->_product);
        if (!empty($merchantReturnPolicyData)) {
            $data['hasMerchantReturnPolicy'] = $merchantReturnPolicyData;
        }

        $condition = $this->helperProductDataProvider->getConditionValue($this->_product);
        if ($condition) {
            $data['itemCondition'] = $condition;
        }

        return $data;
    }

    /**
     * @return float
     */
    protected function getPrice()
    {
        $price = $this->_product->getPriceInfo()->getPrice('final_price')->getAmount()->__toString();

        if ($price) {
            $price = $this->priceCurrency->round($price);
        }

        return $price;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    protected function getProductCollection()
    {
        $productList = $this->layout->getBlock('category.products.list');

        if (is_object($productList) && ($productList instanceof \Magento\Catalog\Block\Product\ListProduct)) {
            return $productList->getLoadedProductCollection();
        }

        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->layout->getBlock('product_list_toolbar_pager');
        if (!is_object($pager)) {
            $pager = $this->getPagerFromToolbar();
        } elseif (!$pager->getCollection()) {
            $pager = $this->getPagerFromToolbar();
        }

        if (!is_object($pager)) {
            return null;
        }

        return $pager->getCollection();
    }

    /**
     *
     * @return \Magento\Catalog\Block\Product\ListProduct|null
     */
    protected function getPagerFromToolbar()
    {
        $toolbar = $this->layout->getBlock('product_list_toolbar');
        if (is_object($toolbar)) {
            $pager = $toolbar->getChild('product_list_toolbar_pager');
        }

        return !empty($pager) ? $pager : null;
    }

    /**
     * @return bool
     */
    protected function isNoindexPage()
    {
        $robots = $this->pageConfig->getRobots();

        if ($robots && stripos($robots, 'noindex') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getGoogleAssistantJsonData()
    {
        $data['@context']         = 'http://schema.org/';
        $data['@type']            = 'WebPage';
        $speakable                = [];
        $speakable['@type']       = 'SpeakableSpecification';
        $speakable['cssSelector'] = explode(',', $this->helperCategory->getGaCssSelectors());
        $speakable['xpath']       = ['/html/head/title'];
        $data['speakable']        = $speakable;

        return $data;
    }
}
