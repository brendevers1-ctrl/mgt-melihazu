<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\SeoMarkup\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * SEO Markup Product Helper
 */
class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**@#+
     * XML config setting paths
     */
    const XML_PATH_PRODUCT_ENABLED                      = 'mageworx_seo/markup/product/rs_enabled';
    const XML_PATH_PRODUCT_ENABLED_FOR_SPECIFIC_PRODUCT = 'mageworx_seo/markup/product/rs_enabled_for_specific_product';
    const XML_PATH_CATEGORY_ENABLED                     = 'mageworx_seo/markup/product/category_enabled';
    const XML_PATH_CATEGORY_DEEPEST                     = 'mageworx_seo/markup/product/category_deepest';
    const XML_PATH_ADD_REVIEWS                          = 'mageworx_seo/markup/product/add_reviews';
    const XML_PATH_BEST_RATING                          = 'mageworx_seo/markup/product/best_rating';
    const XML_PATH_DISABLE_DEFAULT_REVIEW               = 'mageworx_seo/markup/product/disable_default_review';
    const XML_PATH_DESCRIPTION_CODE                     = 'mageworx_seo/markup/product/description_code';
    const XML_PATH_CROP_HTML_IN_DESCRIPTION             = 'mageworx_seo/markup/product/crop_html_in_description';
    const XML_PATH_SKU_ENABLED                          = 'mageworx_seo/markup/product/sku_enabled';
    const XML_PATH_SKU_CODE                             = 'mageworx_seo/markup/product/sku_code';
    const XML_PATH_COLOR_ENABLED                        = 'mageworx_seo/markup/product/color_enabled';
    const XML_PATH_COLOR_CODE                           = 'mageworx_seo/markup/product/color_code';
    const XML_PATH_WEIGHT_ENABLED                       = 'mageworx_seo/markup/product/weight_enabled';
    const XML_PATH_SAME_AS_ENABLED                      = 'mageworx_seo/markup/product/same_as_enabled';
    const XML_PATH_IS_RELATED_TO_ENABLED                = 'mageworx_seo/markup/product/is_related_to_enabled';
    const XML_PATH_WEIGHT_UNIT                          = 'mageworx_seo/markup/product/weight_unit';
    const XML_PATH_MANUFACTURER_ENABLED                 = 'mageworx_seo/markup/product/manufacturer_enabled';
    const XML_PATH_MANUFACTURER_CODE                    = 'mageworx_seo/markup/product/manufacturer_code';
    const XML_PATH_BRAND_ENABLED                        = 'mageworx_seo/markup/product/brand_enabled';
    const XML_PATH_BRAND_CODE                           = 'mageworx_seo/markup/product/brand_code';
    const XML_PATH_MODEL_ENABLED                        = 'mageworx_seo/markup/product/model_enabled';
    const XML_PATH_MODEL_CODE                           = 'mageworx_seo/markup/product/model_code';
    const XML_PATH_GTIN_ENABLED                         = 'mageworx_seo/markup/product/gtin_enabled';
    const XML_PATH_GTIN_CODE                            = 'mageworx_seo/markup/product/gtin_code';
    const XML_PATH_PRODUCT_ID_CODE                      = 'mageworx_seo/markup/product/product_id_code';
    const XML_PATH_CONDITION_ENABLED                    = 'mageworx_seo/markup/product/condition_enabled';
    const XML_PATH_CONDITION_CODE                       = 'mageworx_seo/markup/product/condition_code';
    const XML_PATH_CONDITION_NEW                        = 'mageworx_seo/markup/product/condition_value_new';
    const XML_PATH_CONDITION_REF                        = 'mageworx_seo/markup/product/condition_value_refurbished';
    const XML_PATH_CONDITION_USED                       = 'mageworx_seo/markup/product/condition_value_used';
    const XML_PATH_CONDITION_DAMAGED                    = 'mageworx_seo/markup/product/condition_value_damaged';
    const XML_PATH_CONDITION_REFURBISHED                = 'mageworx_seo/markup/product/condition_value_refurbished';
    const XML_PATH_CONDITION_DEFAULT                    = 'mageworx_seo/markup/product/condition_value_default';
    const XML_PATH_MERCHANT_RETURN_POLICY_ENABLED       = 'mageworx_seo/markup/product/merchant_return_policy/enabled';
    const XML_PATH_MERCHANT_RETURN_POLICY_CODE          = 'mageworx_seo/markup/product/merchant_return_policy/code';
    const XML_PATH_MERCHANT_RETURN_POLICY_CATEGORY      = 'mageworx_seo/markup/product/merchant_return_policy/category';
    const XML_PATH_MERCHANT_RETURN_POLICY_DAYS          = 'mageworx_seo/markup/product/merchant_return_policy/days';
    const XML_PATH_MERCHANT_RETURN_POLICY_METHOD        = 'mageworx_seo/markup/product/merchant_return_policy/method';
    const XML_PATH_MERCHANT_RETURN_POLICY_FEES          = 'mageworx_seo/markup/product/merchant_return_policy/fees';
    const XML_PATH_IMAGE_PRODUCT_MODE                   = 'mageworx_seo/markup/product/image_product_mode';

    const XML_PATH_MERCHANT_RETURN_POLICY_SHIPPING_FEES_AMOUNT =
        'mageworx_seo/markup/product/merchant_return_policy/shipping_fees_amount';
    const XML_PATH_MERCHANT_RETURN_POLICY_APPLICABLE_COUNTRY   =
        'mageworx_seo/markup/product/merchant_return_policy/country';

    const XML_PATH_ENABLED_CUSTOM_PROPERTIES             = 'mageworx_seo/markup/product/custom_prorerty_enabled';
    const XML_PATH_CUSTOM_PROPERTIES                     = 'mageworx_seo/markup/product/custom_prorerties';
    const XML_PATH_PRODUCT_PAGE_GOOGLE_ASSISTANT_ENABLED = 'mageworx_seo/markup/product/ga_enabled';
    const XML_PATH_USE_MULTIPLE_OFFER                    = 'mageworx_seo/markup/product/use_multiple_offer';
    const XML_PATH_CSS_SELECTOR                          = 'mageworx_seo/markup/product/ga_css_selector';

    const XML_PATH_SPECIAL_PRICE_FUNCTIONALITY     = 'mageworx_seo/markup/product/special_price_functionality';
    const XML_PATH_PRICE_VALID_UNTIL_DEFAULT_VALUE = 'mageworx_seo/markup/product/price_valid_until_default_value';

    const XML_PATH_SHIPPING_DETAILS_ENABLED  = 'mageworx_seo/markup/product/shipping_details/enabled';
    const XML_PATH_SHIPPING_COST             = 'mageworx_seo/markup/product/shipping_details/cost';
    const XML_PATH_MAX_SHIPPING_COST         = 'mageworx_seo/markup/product/shipping_details/max_cost';
    const XML_PATH_SHIPPING_COUNTRY          = 'mageworx_seo/markup/product/shipping_details/country';
    const XML_PATH_MIN_DAYS_FOR_TRANSIT_TIME = 'mageworx_seo/markup/product/shipping_details/min_days_for_transit_time';
    const XML_PATH_MAX_DAYS_FOR_TRANSIT_TIME = 'mageworx_seo/markup/product/shipping_details/max_days_for_transit_time';
    const XML_PATH_FREE_SHIPPING_ENABLED     = 'mageworx_seo/markup/product/shipping_details/free_shipping_enabled';
    const XML_PATH_FREE_SHIPPING_CODE        = 'mageworx_seo/markup/product/shipping_details/free_shipping_code';

    const XML_PATH_SHIPPING_DETAILS_BUSINESS_DAYS = 'mageworx_seo/markup/product/shipping_details/business_days';
    const XML_PATH_SHIPPING_DETAILS_CUTOFF_TIME   = 'mageworx_seo/markup/product/shipping_details/cutoff_time';
    const XML_PATH_MIN_DAYS_FOR_HANDLING_TIME     =
        'mageworx_seo/markup/product/shipping_details/min_days_for_handling_time';
    const XML_PATH_MAX_DAYS_FOR_HANDLING_TIME     =
        'mageworx_seo/markup/product/shipping_details/max_days_for_handling_time';

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helperDirectoryData;

    /**
     * Product constructor.
     *
     * @param \Magento\Tax\Model\Config $helperTax
     * @param \Magento\Directory\Helper\Data $helperDirectoryData
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Directory\Helper\Data $helperDirectoryData,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->helperDirectoryData = $helperDirectoryData;
        parent::__construct($context);
    }

    /**
     * Check if enabled in the rich snippets
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled in the rich snippets for products without offers and rating
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabledForSpecificProduct(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ENABLED_FOR_SPECIFIC_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled in the google assistant
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isGaEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_PAGE_GOOGLE_ASSISTANT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getImageMode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_IMAGE_PRODUCT_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve the css selector
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGaCssSelectors(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CSS_SELECTOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function useMultipleOffer(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_MULTIPLE_OFFER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if category enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isCategoryEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if use deepest category
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isCategoryDeepest(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_DEEPEST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Shipping Details enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isShippingDetailsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHIPPING_DETAILS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Free Shipping enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isFreeShippingEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FREE_SHIPPING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Free Shipping code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFreeShippingCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_FREE_SHIPPING_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Shipping cost
     *
     * @param int|null $storeId
     * @return float|null
     */
    public function getShippingCost(?int $storeId = null): ?float
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_COST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim($value) === '') {
            return null;
        }

        return (float)$value;
    }

    /**
     * Retrieve Max Shipping cost
     *
     * @param int|null $storeId
     * @return float|null
     */
    public function getMaxShippingCost(?int $storeId = null): ?float
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_SHIPPING_COST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim($value) === '') {
            return null;
        }

        return (float)$value;
    }

    /**
     * Retrieve Shipping country
     *
     * @param int|null $storeId
     * @return string
     */
    public function getShippingCountry(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Min Days for Handling Time
     *
     * @param int|null $storeId
     * @return int|null
     */
    public function getMinDaysForHandlingTime(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MIN_DAYS_FOR_HANDLING_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return (int)$value;
    }

    /**
     * Retrieve Max Days for Handling Time
     *
     * @param int|null $storeId
     * @return int|null
     */
    public function getMaxDaysForHandlingTime(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_DAYS_FOR_HANDLING_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return (int)$value;
    }

    /**
     * Retrieve Min Days for Transit Time
     *
     * @param int|null $storeId
     * @return int|null
     */
    public function getMinDaysForTransitTime(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MIN_DAYS_FOR_TRANSIT_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return (int)$value;
    }

    /**
     * Retrieve Max Days for Transit Time
     *
     * @param int|null $storeId
     * @return int|null
     */
    public function getMaxDaysForTransitTime(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_DAYS_FOR_TRANSIT_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return (int)$value;
    }

    /**
     * Retrieve Business Days for Shipping Details
     *
     * @param int|null $storeId
     * @return array
     */
    public function getBusinessDaysForShippingDetails(?int $storeId = null): array
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_DETAILS_BUSINESS_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return explode(',', $value);
    }

    /**
     * Retrieve Cutoff Time for Shipping Details
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCutoffTimeForShippingDetails(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_DETAILS_CUTOFF_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Merchant Return Policy enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isMerchantReturnPolicyEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MERCHANT_RETURN_POLICY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantReturnPolicyCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy applicable country
     *
     * @param int|null $storeId
     * @return array
     */
    public function getMerchantReturnPolicyApplicableCountry(?int $storeId = null): array
    {
        $pagesString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_APPLICABLE_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $arrayRaw    = array_map('trim', explode(',', $pagesString));

        return array_filter($arrayRaw);
    }

    /**
     * Retrieve Merchant Return Policy category
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantReturnPolicyCategory(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy days
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMerchantReturnPolicyDays(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy method
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantReturnPolicyMethod(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy fees
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantReturnPolicyFees(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_FEES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Merchant Return Policy shipping fees amount
     *
     * @param int|null $storeId
     * @return float|null
     */
    public function getShippingFeesAmount(?int $storeId = null): ?float
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_RETURN_POLICY_SHIPPING_FEES_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        return (float)$value;
    }

    /**
     * Check if condition enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isConditionEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve description code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDescriptionCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DESCRIPTION_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if crops HTML tags in description enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getIsCropHtmlInDescription(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CROP_HTML_IN_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if SKU enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isSkuEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SKU_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve SKU code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSkuCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SKU_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled reviews markup
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isReviewsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ADD_REVIEWS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve the best rating
     *
     * @param int|null $storeId
     * @return int
     */
    public function getBestRating(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BEST_RATING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if disabled default review markup
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isDisableDefaultReview(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_DEFAULT_REVIEW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if use Special Price functionality
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isUseSpecialPriceFunctionality(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SPECIAL_PRICE_FUNCTIONALITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve priceValidUntil default value
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPriceValidUntilDefaultValue(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRICE_VALID_UNTIL_DEFAULT_VALUE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve productID code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getProductIdCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ID_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition value for new item
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionValueForNew(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_NEW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition value for refurbished item
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionValueForRefurbished(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_REFURBISHED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition value for damaged item
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionValueForDamaged(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_DAMAGED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition value for used item
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionValueForUsed(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_USED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve condition value for used item
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConditionDefaultValue(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONDITION_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if color enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isColorEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_COLOR_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve color code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getColorCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_COLOR_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if manufacturer enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isManufacturerEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_MANUFACTURER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve manufacturer code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getManufacturerCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MANUFACTURER_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if brand enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isBrandEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_BRAND_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve brand code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getBrandCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_BRAND_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if model enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isModelEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_MODEL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve model code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getModelCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MODEL_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if gtin enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isGtinEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_GTIN_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve gtin code
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGtinCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_GTIN_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if weight enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isWeightEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_WEIGHT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if sameAs enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isSameAsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SAME_AS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if isRelatedTo enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRelatedToEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_RELATED_TO_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve weight unit
     *
     * @param int|null $storeId
     * @return string
     */
    public function getWeightUnit(?int $storeId = null): string
    {
        return (string)$this->helperDirectoryData->getWeightUnit();
    }

    /**
     * Check if custom properties enabled
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isCustomPropertiesEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_CUSTOM_PROPERTIES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param int|null $storeId
     * @return array
     */
    public function getCustomProperties(?int $storeId = null): array
    {
        if (!$this->isCustomPropertiesEnabled($storeId)) {
            return [];
        }

        $rawString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_PROPERTIES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $string    = trim($rawString);
        $pairArray = array_filter(preg_split('/\r?\n/', $string));
        if($pairArray === false) {
            return [];
        }
        $pairArray = array_filter(array_map('trim', $pairArray));

        $ret = [];
        foreach ($pairArray as $pair) {
            $pair    = trim((string)$pair, ',');
            $explode = explode(',', $pair);
            if (is_array($explode) && count($explode) >= 2) {
                $key = trim($explode[0]);
                $val = trim($explode[1]);
                if ($key && $val) {
                    $ret[$key] = $val;
                }
            }
        }

        return $ret;
    }
}
