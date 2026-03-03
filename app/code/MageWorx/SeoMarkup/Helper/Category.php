<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Helper;

use Magento\Store\Model\ScopeInterface;

class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**@#+
     * XML config setting paths
     */
    const XML_PATH_CATEGORY_RICHSNIPPET_ENABLED           = 'mageworx_seo/markup/category/rs_enabled';
    const XML_PATH_CATEGORY_USE_OFFERS                    = 'mageworx_seo/markup/category/add_product_offers';
    const XML_PATH_CATEGORY_ROBOTS_RESTRICTION            = 'mageworx_seo/markup/category/robots_restriction';
    const XML_PATH_CATEGORY_PAGE_GOOGLE_ASSISTANT_ENABLED = 'mageworx_seo/markup/category/ga_enabled';
    const XML_PATH_CSS_SELECTOR                           = 'mageworx_seo/markup/category/ga_css_selector';
    const XML_PATH_IMAGE_PRODUCT_MODE                     = 'mageworx_seo/markup/product/image_product_mode';

    /**
     * Check if enabled in the rich snippets
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabled($storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_RICHSNIPPET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if enabled offer
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isUseOfferForCategoryProducts($storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_USE_OFFERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if add by robots
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isUseCategoryRobotsRestriction($storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_ROBOTS_RESTRICTION,
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
    public function isGaEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_PAGE_GOOGLE_ASSISTANT_ENABLED,
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
    public function getGaCssSelectors($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CSS_SELECTOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
