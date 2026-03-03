<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Helper;

use Magento\Store\Model\ScopeInterface;
use MageWorx\SeoMarkup\Model\Source\SellerPages;

/**
 * SEO Markup Seller Helper
 */
class Seller extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**@#+
     * XML config setting paths
     */
    const XML_PATH_SELLER_ENABLED       = 'mageworx_seo/markup/seller/rs_enabled';
    const XML_PATH_SELLER_TYPE          = 'mageworx_seo/markup/seller/type';
    const XML_PATH_SELLER_SHOW_ON_PAGES = 'mageworx_seo/markup/seller/show_on_pages';
    const XML_PATH_SELLER_NAME          = 'mageworx_seo/markup/seller/name';
    const XML_PATH_SELLER_IMAGE         = 'mageworx_seo/markup/seller/image';
    const XML_PATH_SELLER_DESCRIPTION   = 'mageworx_seo/markup/seller/description';
    const XML_PATH_SELLER_OPENING_HOURS = 'mageworx_seo/markup/seller/opening_hours';
    const XML_PATH_SELLER_PHONE         = 'mageworx_seo/markup/seller/phone';
    const XML_PATH_SELLER_FAX           = 'mageworx_seo/markup/seller/fax';
    const XML_PATH_SELLER_EMAIL         = 'mageworx_seo/markup/seller/email';
    const XML_PATH_SELLER_COUNTRY       = 'mageworx_seo/markup/seller/country';
    const XML_PATH_SELLER_LOCATION      = 'mageworx_seo/markup/seller/location';
    const XML_PATH_SELLER_REGION        = 'mageworx_seo/markup/seller/region';
    const XML_PATH_SELLER_STREET        = 'mageworx_seo/markup/seller/street';
    const XML_PATH_SELLER_POST_CODE     = 'mageworx_seo/markup/seller/post_code';
    const XML_PATH_SELLER_PRICE_RANGE   = 'mageworx_seo/markup/seller/price_range';
    const XML_PATH_SAME_AS_LINKS        = 'mageworx_seo/markup/seller/same_as_links';


    /**
     * Check if enabled in the rich snippets
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SELLER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve seller type
     *
     * @param int|null $storeId
     * @return string
     */
    public function getType(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_TYPE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve pages where seller markup will be added
     * @see https://www.searchenginejournal.com/google-do-not-put-organization-schema-markup-on-every-page/289981/
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPageType(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_SHOW_ON_PAGES,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isShowOnlyForHomePage(?int $storeId = null): bool
    {
        return $this->getPageType($storeId) == SellerPages::HOME_PAGE;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isShowForAllPages(?int $storeId = null): bool
    {
        return $this->getPageType($storeId) == SellerPages::ALL_PAGES;
    }

    /**
     * Retrieve seller name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getName(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_NAME,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller image
     *
     * @param int|null $storeId
     * @return string
     */
    public function getImage(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_IMAGE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller description
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDescription(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_DESCRIPTION,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller opening hours
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOpeningHours(?int $storeId = null): array
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_SELLER_OPENING_HOURS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $value = array_filter((array)preg_split('/\r?\n/', $value));
        $value = array_map('trim', $value);

        return array_filter($value);
    }

    /**
     * Retrieve seller phone number
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPhone(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_PHONE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller fax number
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFax(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_FAX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller e-mail
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEmail(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_EMAIL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller country code (ISO-2 format)
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCountryCode(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SELLER_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve seller location
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLocation(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_LOCATION,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller region address
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRegionAddress(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_REGION,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller region address
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStreetAddress(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_STREET,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve seller post address
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPostCode(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_POST_CODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * Retrieve social links
     *
     * @param int|null $storeId
     * @return array
     */
    public function getSameAsLinks(?int $storeId = null): array
    {
        $linksString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_SAME_AS_LINKS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $linksArray  = array_filter((array)preg_split('/\r?\n/', $linksString));
        $linksArray  = array_map('trim', $linksArray);

        return array_filter($linksArray);
    }

    /**
     * Retrieve seller price range
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPriceRange(?int $storeId = null): string
    {
        return trim((string)
            $this->scopeConfig->getValue(
                self::XML_PATH_SELLER_PRICE_RANGE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }
}
