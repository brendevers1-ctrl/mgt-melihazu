<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Converter;

use Magento\Directory\Model\Currency;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Store\Model\ScopeInterface;
use MageWorx\SeoXTemplates\Model\Converter;
use Magento\Framework\Pricing\Helper\Data as HelperPrice;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use MageWorx\SeoXTemplates\Helper\Converter as HelperConverter;
use Magento\Tax\Helper\Data as HelperTax;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Area;

/**
 * Product Data Converter Class
 *
 * @property \Magento\Catalog\Model\Product $item
 */
abstract class Product extends Converter
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var HelperTax
     */
    protected $helperTax;

    /**
     *
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $helperCatalog;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var LocalResolverInterface
     */
    protected $localeResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     *
     * @var array
     */
    protected static $_variablesData = [];

    /**
     *
     * @var array
     */
    protected $_dynamicVariables = ['category', 'categories'];

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param HelperConverter $helperConverter
     * @param \MageWorx\SeoXTemplates\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param Registry $registry
     * @param HelperPrice $helperPrice
     * @param HelperTax $helperTax
     * @param \Magento\Catalog\Helper\Data $helperCatalog
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param ScopeConfigInterface $config
     * @param LocalResolverInterface $localeResolver
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HelperData $helperData,
        HelperConverter $helperConverter,
        \MageWorx\SeoXTemplates\Model\ResourceModel\Category $resourceCategory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        Registry $registry,
        HelperPrice $helperPrice,
        HelperTax $helperTax,
        \Magento\Catalog\Helper\Data $helperCatalog,
        \Magento\Store\Model\App\Emulation $emulation,
        ScopeConfigInterface $config,
        LocalResolverInterface $localeResolver
    ) {
        parent::__construct($storeManager, $helperData, $helperConverter, $resourceCategory, $request);
        $this->priceCurrency   = $priceCurrency;
        $this->registry        = $registry;
        $this->helperPrice     = $helperPrice;
        $this->resourceProduct = $resourceProduct;
        $this->helperTax       = $helperTax;
        $this->helperCatalog   = $helperCatalog;
        $this->emulation       = $emulation;
        $this->config          = $config;
        $this->localeResolver  = $localeResolver;
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->item->getStoreId();

        return $this->helperPrice->currencyByStore($price, $store, false);
    }

    /**
     * Retrieve converted string by template code
     *
     * @param array $vars
     * @param string $templateCode
     * @return string
     */
    protected function __convert($vars, $templateCode)
    {
        $convertValue = $templateCode;

        foreach ($vars as $key => $params) {
            if (!$this->isDynamically && $this->_issetDynamicAttribute($params['attributes'])) {
                $value = $key;
            } else {
                foreach ($params['attributes'] as $attributeCode) {
                    switch ($attributeCode) {
                        case 'name':
                            $value = $this->_convertName($attributeCode);
                            break;
                        case 'category':
                            $value = $this->_convertCategory();
                            break;
                        case 'categories':
                            $value = $this->_convertCategories();
                            break;
                        case 'store_view_name':
                            $value = $this->_convertStoreViewName();
                            break;
                        case 'store_name':
                            $value = $this->_convertStoreName();
                            break;
                        case 'website_name':
                            $value = $this->_convertWebsiteName();
                            break;
                        case 'price':
                            $value = $this->_convertPrice();
                            break;
                        case 'special_price':
                            $value        = '';
                            $specialPrice = (double)$this->item->getSpecialPrice();

                            if ($specialPrice) {
                                $value = $this->_convertSpecialPrice();
                            }
                            break;
                        case 'tier_price_min':
                            $value = $this->_convertTierPrice();

                            break;
                        default:
                            $value = $this->_convertAttribute($attributeCode);
                            break;
                    }

                    if ($value) {
                        $prefix = $this->helperConverter->randomizePrefix($params['prefix']);
                        $suffix = $this->helperConverter->randomizeSuffix($params['suffix']);
                        $value  = $prefix . $value . $suffix;
                        break;
                    }
                }
            }

            $convertValue = str_replace($key, (string)$value, $convertValue);
        }

        return $this->_render($convertValue);
    }

    /**
     * Retrieve converted string
     *
     * @param string $attribute
     * @return string
     */
    protected function _convertName($attribute)
    {
        return $this->_convertAttribute($attribute);
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreViewName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getGroup()->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertWebsiteName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getWebsite()->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertCategory()
    {
        $params = $this->_getRequestParams();
        if (empty($params['category'])) {
            return '';
        }

        if (!is_callable([$this->resourceCategory, 'getAttributeRawValue'])) {
            return '';
        } else {
            if (isset(self::$_variablesData['category'])) {
                $value = self::$_variablesData['category'];
            } elseif (isset(self::$_variablesData['categories'])) {
                list($value) = explode(', ', self::$_variablesData['categories']);
            } else {
                $value = $this->_getRawCategoryAttributeValue($params['category'], 'name');
            }

            $value = ($value == 'Root Catalog') ? '' : $value;

            self::$_variablesData['category'] = $value;

            return $value;
        }

        return '';
    }

    /**
     *
     * @return string
     */
    protected function _convertCategories()
    {
        $categoryId = $this->_getCategoryId();

        if (!is_callable([$this->resourceCategory, 'getAttributeRawValue'])) {
            return '';
        } else {
            if (isset(self::$_variablesData['categories'])) {
                return self::$_variablesData['categories'];
            }

            $path      = $this->_getRawCategoryAttributeValue($categoryId, 'path');
            $pathArray = empty($path['path']) ? [] : array_reverse(explode('/', $path['path']));
            $separator = $this->helperData->getTitleSeparator($this->item->getStoreId());

            $names = [];
            foreach ($pathArray as $id) {
                if ($categoryId == $id && !empty(self::$_variablesData['category'])) {
                    $category = self::$_variablesData['category'];
                } else {
                    $category = $this->_getRawCategoryAttributeValue($id, 'name');
                }
                if ($category && $category != 'Root Catalog' && $category != 'Default Category') {
                    $names[$id] = $category;
                }
            }
            $value                              = trim(implode($separator, $names));
            self::$_variablesData['categories'] = $value;

            return $value;
        }

        return '';
    }

    /**
     * @return int|null
     */
    protected function _getCategoryId()
    {
        $params = $this->_getRequestParams();

        if (!empty($params['category'])) {
            return $params['category'];
        }

        // When the category loaded by data from customer session.
        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory) {
            return $currentCategory->getId();
        }

        return null;
    }

    /**
     * @param string $path
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCurrencyForCurrentStore(string $path)
    {
        $store = $this->storeManager->getStore();
        $code  = $this->config->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode());

        return $code;
    }

    /**
     * Retrieve converted string
     *
     * @return string
     */
    protected function _convertPrice()
    {
        $this->emulation->startEnvironmentEmulation((string)$this->item->getStoreId(), Area::AREA_FRONTEND, false);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $storeBaseCurrencyCode    = $this->getCurrencyForCurrentStore(Currency::XML_PATH_CURRENCY_BASE);
        $storeDefaultCurrencyCode = $this->getCurrencyForCurrentStore(Currency::XML_PATH_CURRENCY_DEFAULT);

        if ($storeBaseCurrencyCode != $storeDefaultCurrencyCode) {
            $store->setCurrentCurrencyCode($storeBaseCurrencyCode);
        } else {
            $store->setCurrentCurrencyCode($storeDefaultCurrencyCode);
        }

        $priceAmount = $this->item->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount();
        $price       = $priceAmount->__toString();

        $this->emulation->stopEnvironmentEmulation();

        $currentLocaleCode = $this->localeResolver->getLocale();
        $newLocaleCode     = $this->config->getValue(
            $this->localeResolver->getDefaultLocalePath(),
            ScopeInterface::SCOPE_STORE,
            $this->item->getStoreId()
        );
        $this->localeResolver->setLocale($newLocaleCode);

        if ((double)$price) {
            $price = $this->priceCurrency->convertAndFormat(
                $price,
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $storeDefaultCurrencyCode
            );
        } else {
            $price = '';
        }

        $this->localeResolver->setLocale($currentLocaleCode);

        return $price;
    }

    /**
     * Retrieve converted string
     *
     * @return string
     */
    protected function _convertPriceForBundle()
    {
        return false;
    }

    /**
     * Retrieve converted string
     *
     * @param int $includingTax
     * @return string
     */
    protected function _convertPriceForGrouped()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function _convertPriceForConfigurableProduct()
    {
        return false;
    }

    /**
     * Retrieve converted string
     *
     * @return string
     */
    protected function _convertSpecialPrice()
    {
        return $this->_convertPrice();
    }

    /**
     * @return float|string
     */
    protected function _convertTierPrice()
    {
        $this->emulation->startEnvironmentEmulation((string)$this->item->getStoreId(), Area::AREA_FRONTEND, false);

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $storeBaseCurrencyCode    = $this->getCurrencyForCurrentStore(Currency::XML_PATH_CURRENCY_BASE);
        $storeDefaultCurrencyCode = $this->getCurrencyForCurrentStore(Currency::XML_PATH_CURRENCY_DEFAULT);

        if ($storeBaseCurrencyCode != $storeDefaultCurrencyCode) {
            $store->setCurrentCurrencyCode($storeBaseCurrencyCode);
        } else {
            $store->setCurrentCurrencyCode($storeDefaultCurrencyCode);
        }

        $prices = [];

        $tierPrices    = $this->item->getTierPrice();
        $itemWebsiteId = $this->storeManager->getStore($this->item->getStoreId())->getWebsiteId();

        foreach ($tierPrices as $tierPriceData) {

            // NOT LOGGED IN or ALL GROUPS
            if ($tierPriceData['cust_group'] == '0' || $tierPriceData['cust_group'] == 32000) {

                if ($tierPriceData['website_id'] == '0' || $tierPriceData['website_id'] == $itemWebsiteId) {
                    $prices[] = (double)$tierPriceData['price'];
                }
            }
        }

        if ($prices) {

            $price = $this->addTaxToPriceIfNeeded(min($prices));

            if ($price > 0) {
                if ($storeBaseCurrencyCode != $storeDefaultCurrencyCode) {
                    $price = $this->priceCurrency->convertAndFormat(
                        $price,
                        false,
                        PriceCurrencyInterface::DEFAULT_PRECISION,
                        null,
                        $storeDefaultCurrencyCode
                    );
                } else {
                    $price = $this->priceCurrency->format(
                        $price,
                        false,
                        PriceCurrencyInterface::DEFAULT_PRECISION,
                        null,
                        $storeDefaultCurrencyCode
                    );
                }
            } else {
                $price = '';
            }
        } else {
            $price = '';
        }

        $this->emulation->stopEnvironmentEmulation();

        return $price;
    }

    /**
     * @param float $price
     * @return float
     */
    protected function addTaxToPriceIfNeeded($price)
    {
        if ($price > 0 && $this->helperData->getUsePriceInTax($this->item->getStoreId())) {
            return $this->helperCatalog->getTaxPrice($this->item, $price);
        }

        return $price;
    }

    /**
     * Retrieve converted string
     *
     * @param string $attributeCode
     * @return string
     */
    protected function _convertAttribute($attributeCode)
    {
        $tempValue = '';
        $value     = $this->item->getData($attributeCode);
        if ($_attr = $this->item->getResource()->getAttribute($attributeCode)) {
            $_attr->setStoreId($this->item->getStoreId());
            if ($_attr->usesSource()) {
                $tempValue = $_attr->setStoreId($this->item->getStoreId())->getSource()->getOptionText(
                    $this->item->getData($attributeCode)
                );
            }
        }
        if ($tempValue) {
            $value = $tempValue;
        }
        if (!$value) {
            if ($this->item->getTypeId() == 'configurable') {
                $productAttributeOptions = $this->item->getTypeInstance(true)->getConfigurableAttributesAsArray(
                    $this->item
                );
                $attributeOptions        = [];
                foreach ($productAttributeOptions as $productAttribute) {
                    if ($productAttribute['attribute_code'] == $attributeCode) {
                        foreach ($productAttribute['values'] as $attribute) {
                            $attributeOptions[] = $attribute['store_label'];
                        }
                    }
                }
                if (count($attributeOptions) == 1) {
                    $value = array_shift($attributeOptions);
                }
            } else {
                $value = $this->item->getData($attributeCode);
            }
        }

        return is_array($value) ? implode(', ', $value) : $value;
    }

    /**
     *
     * @param string $converValue
     * @return string
     */
    protected function _render($convertValue)
    {
        return trim($convertValue);
    }

    /**
     * Check if we have display in catalog prices including tax
     *
     * @param int|Store
     * @return bool
     */
    public function displayPriceIncludingTax($store)
    {
        return $this->getPriceDisplayType($store) == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param int|Store $store
     * @return int
     */
    public function getPriceDisplayType($store)
    {
        return $this->helperTax->getPriceDisplayType($store);
    }

    /**
     * @param array $attributes
     * @return boolean
     */
    protected function _issetDynamicAttribute($attributes)
    {
        return (bool)array_intersect($this->_dynamicVariables, $attributes);
    }

    /**
     * @param string $templateCode
     * @return bool
     */
    protected function stopProcess($templateCode)
    {
        if (!$this->isDynamically) {
            return false;
        }

        $isNotFound = true;

        foreach ($this->_dynamicVariables as $variable) {
            if (strpos($templateCode, '[' . trim($variable) . ']') !== false) {
                $isNotFound = false;
            }

            if (strpos($templateCode, '{' . trim($variable) . '}') !== false) {
                $isNotFound = false;
            }
        }

        return $isNotFound;
    }
}
