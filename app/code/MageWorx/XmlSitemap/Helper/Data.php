<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * XML Sitemap data helper
 *
 */

namespace MageWorx\XmlSitemap\Helper;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\SeoAll\Model\MetaRobotsDecoder;
use MageWorx\XmlSitemap\Model\Source\ProductImageSource;

class Data extends \Magento\Sitemap\Helper\Data
{
    /**
     * XML config path show homepage optimization enabled
     */
    const XML_PATH_HOMEPAGE_OPTIMIZE = 'mageworx_seo/xml_sitemap/homepage_optimize';

    /**
     * XML config path links enabled
     */
    const XML_PATH_SHOW_LINKS = 'mageworx_seo/xml_sitemap/enable_additional_links';

    /**
     * XML config path links
     */
    const XML_PATH_ADDITIONAL_LINKS = 'mageworx_seo/xml_sitemap/additional_links';

    /**
     * XML config setting change frequency
     */
    const XML_PATH_ADDITIONAL_LINK_CHANGEFREQ = 'mageworx_seo/xml_sitemap/additional_links_changefreq';

    /**
     * XML config setting change priority
     */
    const XML_PATH_ADDITIONAL_LINK_PRIORITY = 'mageworx_seo/xml_sitemap/additional_links_priority';

    /**
     * XML config path trailing slash for home page URL
     */
    const XML_PATH_TRAILING_SLASH_FOR_HOME = 'mageworx_seo/common_sitemap/trailing_slash_home_page';

    /**
     * XML config path trailing slash for URL
     */
    const XML_PATH_TRAILING_SLASH = 'mageworx_seo/common_sitemap/trailing_slash';

    /**
     * XML config path exclude out of stock products
     */
    const XML_PATH_EXCLUDE_OUT_OF_STOCK_PRODUCTS = 'mageworx_seo/xml_sitemap/exclude_out_of_stock_products';

    /**
     * XML config path add Alternate Hreflang URLs
     */
    const XML_PATH_ADD_HREFLANGS = 'mageworx_seo/xml_sitemap/add_hreflangs';

    /**
     * XML config path use css-style
     */
    const XML_PATH_USE_CSS_FOR_XML = 'mageworx_seo/xml_sitemap/use_css_for_xml';

    /**
     * XML config path exclude by meta robots
     */
    const XML_PATH_META_ROBOTS_EXCLUSION = 'mageworx_seo/xml_sitemap/meta_robots_exclusion';

    /**
     * XML config path Enable Validate Urls
     */
    const XML_PATH_ENABLE_VALIDATE_URLS = 'mageworx_seo/xml_sitemap/enable_validate_urls';

    const XML_PATH_IS_CHECK_URLS_AVAILABILITY = 'mageworx_seo/xml_sitemap/check_urls_availability';

    /**
     * XML config path Image Source
     */
    const XML_PATH_PRODUCT_IMAGE_SOURCE = 'mageworx_seo/xml_sitemap/product_image_source';

    const XML_PATH_PRODUCT_VIDEOS_INCLUDE = 'mageworx_seo/xml_sitemap/product_video_include';

    const XML_PATH_CATEGORY_IMAGES_INCLUDE = 'mageworx_seo/xml_sitemap/category_image_include';


    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DateTime
     */
    protected $modelDate;

    /**
     * @var MetaRobotsDecoder
     */
    protected $metaRobotsDecoder;

    /**
     * @var null|int
     */
    protected $storeId = null;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param DateTime $modelDate
     * @param ObjectManagerInterface $objectManager
     * @param MetaRobotsDecoder $metaRobotsDecoder
     */
    public function __construct(
        Context $context,
        DateTime $modelDate,
        ObjectManagerInterface $objectManager,
        MetaRobotsDecoder $metaRobotsDecoder
    ) {
        parent::__construct($context);
        $this->objectManager     = $objectManager;
        $this->modelDate         = $modelDate;
        $this->metaRobotsDecoder = $metaRobotsDecoder;
    }

    /**
     * @param int $storeId
     */
    public function init($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Check if optimization home page URL and priority
     *
     * @return bool
     */
    public function isOptimizeHomePage()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_OPTIMIZE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if show additional links
     *
     * @return bool
     */
    public function isShowLinks()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SHOW_LINKS,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Retrieve additional links
     *
     * @return array
     */
    public function getAdditionalLinks()
    {
        $linksString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINKS,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        $linksArray = array_filter(preg_split('/\r?\n/', $linksString));
        $linksArray = array_map('trim', $linksArray);

        return array_filter($linksArray);
    }

    /**
     * Retrieve additional links as prepared array of \Magento\Framework\Object objects
     *
     * @return array
     */
    public function getAdditionalLinkCollection()
    {
        $links = [];
        foreach ($this->getAdditionalLinks($this->storeId) as $link) {
            $object = new DataObject();
            $object->setUrl($link);
            $object->setUpdatedAt(date('c',strtotime($this->modelDate->gmtDate('Y-m-d H:i:s'))));
            $links[] = $object;
        }

        return $links;
    }

    /**
     * @return string
     */
    public function getCurrentDate()
    {
        $timestamp = strtotime($this->modelDate->gmtDate('Y-m-d H:i:s'));
        return date('c', $timestamp);
    }

    /**
     * Retrieve home page identifier
     *
     * @return string
     */
    public function getHomeIdentifier()
    {
        return (string)$this->scopeConfig->getValue(
            Page::XML_PATH_HOME_PAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get additional link change frequency
     *
     * @return string
     */
    public function getAdditionalLinkChangefreq()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINK_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get additional link priority
     *
     * @param int $storeId
     * @return string
     */
    public function getAdditionalLinkPriority()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_LINK_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Checks if add or crop trailing slash for URL
     *
     * @return int
     */
    public function getTrailingSlash()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Checks if add or crop trailing slash for home page URL
     *
     * @return int
     */
    public function getTrailingSlashForHomePage()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_TRAILING_SLASH_FOR_HOME,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if is Exclude Out Of Stock Products
     *
     * @return bool
     */
    public function isExcludeOutOfStockProducts()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXCLUDE_OUT_OF_STOCK_PRODUCTS,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if use hreflang
     *
     * @return bool
     */
    public function useHreflangs()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADD_HREFLANGS,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if use styles
     *
     * @return bool
     */
    public function isUseCssForXmlSitemap()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_CSS_FOR_XML,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if Enable Validate Urls
     *
     * @return bool
     */
    public function isEnableValidateUrls()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_VALIDATE_URLS,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * @return bool
     */
    public function isCheckUrlsAvailability()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_CHECK_URLS_AVAILABILITY,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * @return string
     * @see \MageWorx\XmlSitemap\Model\Source\ProductImageSource
     */
    public function getProductImageSource()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_IMAGE_SOURCE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        if (!$value) {
            $value = ProductImageSource::CACHE_SOURCE;
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getMetaRobotsExclusion()
    {
        $metaRobotsString = (string)$this->scopeConfig->getValue(
            self::XML_PATH_META_ROBOTS_EXCLUSION,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );

        return $this->metaRobotsDecoder->decodeMetaRobots(
            array_filter(array_map('trim', explode(',', $metaRobotsString)))
        );
    }

    /**
     * Check if include category images
     *
     * @return bool
     */
    public function isCategoryImages()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CATEGORY_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if include product images
     *
     * @return bool
     */
    public function isProductImages()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Check if include product videos
     *
     * @return string
     */
    public function isProductVideos()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_VIDEOS_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get maximum file size in bytes
     *
     * @return int
     */
    public function getSplitSize()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get maximum URLs number
     *
     * @return int
     */
    public function getMaxLinks()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAX_LINES,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * @param string $url
     * @param bool $isHome
     * @return string
     */
    public function trailingSlash($url, $isHome = false)
    {
        if ($isHome) {
            $trailingSlash = $this->getTrailingSlashForHomePage();
        } else {
            $trailingSlash = $this->getTrailingSlash();
        }

        if ($trailingSlash == 1) {
            $url        = rtrim($url);
            $extensions = ['rss', 'html', 'htm', 'xml', 'php'];
            if (substr($url, -1) != '/' && !in_array(substr(strrchr($url, '.'), 1), $extensions)) {
                $url .= '/';
            }
        } elseif ($trailingSlash == 0) {
            $url = rtrim(rtrim($url), '/');
        }

        return $url;
    }
}
