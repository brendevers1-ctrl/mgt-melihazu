<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * SEO Markup Website Helper
 */
class Website extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**@#+
     * XML config setting paths
     */
    const XML_PATH_WEBSITE_RICHSNIPPET_ENABLED      = 'mageworx_seo/markup/website/rs_enabled';
    const XML_PATH_WEBSITE_OPENGRAPH_IMAGE          = 'mageworx_seo/markup/website/og_image';
    const XML_PATH_WEBSITE_OPENGRAPH_APP_ID         = 'mageworx_seo/markup/website/fb_app_id';
    const XML_PATH_WEBSITE_NAME                     = 'mageworx_seo/markup/website/name';
    const XML_PATH_WEBSITE_ABOUT                    = 'mageworx_seo/markup/website/description';
    const XML_PATH_MAGENTO_WEBSITE_NAME             = 'general/store_information/name';
    const XML_PATH_WEBSITE_SEARCH                   = 'mageworx_seo/markup/website/website_use_search';

    /**
     * Check if enabled in the rich snippets
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_RICHSNIPPET_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve facebook logo
     *
     * @param int|null $storeId
     * @return string
     */
    public function getOgImage($storeId = null) {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_OPENGRAPH_IMAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    /**
     * Retrieve facebook app id
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFacebookAppId($storeId = null)
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_OPENGRAPH_APP_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    /**
     * Retrieve store name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getName($storeId = null)
    {
        $storeName = trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));

        if (!$storeName) {
            $storeName = trim((string)$this->scopeConfig->getValue(
                self::XML_PATH_MAGENTO_WEBSITE_NAME,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ));
        }

        return $storeName;
    }

    /**
     * Retrieve store about info
     *
     * @param int|null $storeId
     * @return string
     */
    public function getAboutInfo($storeId = null)
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_ABOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }

    public function isAddWebsiteSearchAction($storeId = null)
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBSITE_SEARCH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
    }
}
