<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * SEO base store url helper
 *
 */

namespace MageWorx\SeoBase\Helper;

use Laminas\Uri\UriFactory;
use Magento\Store\Model\Store;
use Magento\Framework\UrlInterface;

class StoreUrl extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageWorx\SeoBase\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SeoBase\Model\ConfigDataLoader
     */
    protected $configDataLoader;

    /**
     * @var \Magento\Framework\Url\ModifierInterface
     */
    protected $urlModifier;

    /**
     * Base URL cache
     *
     * @var array
     */
    protected $baseUrlCache = [];

    /**
     * StoreUrl constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Data $helperData
     * @param \MageWorx\SeoBase\Model\ConfigDataLoader $configDataLoader
     * @param \Magento\Framework\Url\ModifierInterface $urlModifier
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Model\ConfigDataLoader $configDataLoader,
        \Magento\Framework\Url\ModifierInterface $urlModifier
    ) {
        parent::__construct($context);
        $this->storeManager     = $storeManager;
        $this->helperData       = $helperData;
        $this->configDataLoader = $configDataLoader;
        $this->urlModifier      = $urlModifier;
    }

    /**
     * Get store base url
     *
     * @param int|null $storeId
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreBaseUrl($storeId = null, $type = UrlInterface::URL_TYPE_LINK)
    {
        if (isset($storeId) && $type === UrlInterface::URL_TYPE_LINK
            && $storeId != $this->storeManager->getStore()->getId()
        ) {
            $storeId  = (int)$storeId;
            $secure   = $this->isStoreSecure($storeId);
            $cacheKey = $storeId . '/' . $type . '/' . ($secure ? 'true' : 'false');

            if (isset($this->baseUrlCache[$cacheKey])) {
                return $this->baseUrlCache[$cacheKey];
            }

            $path = $secure ? Store::XML_PATH_SECURE_BASE_LINK_URL : Store::XML_PATH_UNSECURE_BASE_LINK_URL;
            $url  = $this->configDataLoader->getConfigValue($path, $storeId);

            if (isset($url)) {
                $url = $this->updatePathUsingStoreView($url, $storeId);

                if (false !== strpos($url, Store::BASE_URL_PLACEHOLDER)) {
                    $url = str_replace(Store::BASE_URL_PLACEHOLDER, $this->_request->getDistroBaseUrl(), $url);
                }

                $this->baseUrlCache[$cacheKey] = $this->urlModifier->execute(
                    rtrim($url, '/') . '/',
                    \Magento\Framework\Url\ModifierInterface::MODE_BASE
                );

                return $this->baseUrlCache[$cacheKey];
            }
        }

        return rtrim($this->storeManager->getStore($storeId)->getBaseUrl($type), '/') . '/';
    }

    /**
     * @param string $url
     * @param int|null $storeId
     * @param bool $isModifyTrailingSlash
     * @param bool $isHomePage
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl(
        $url,
        $storeId = null,
        $isModifyTrailingSlash = false,
        $isHomePage = false,
        $type = UrlInterface::URL_TYPE_LINK
    ) {
        $url = $this->getStoreBaseUrl($storeId, $type) . ltrim((string)$url, '/');

        return $isModifyTrailingSlash ? $this->trailingSlash($url, $storeId, $isHomePage) : $url;
    }

    /**
     * Retrieve list of active stores
     *
     * @return array
     */
    public function getActiveStores()
    {
        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getIsActive()) {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

    /**
     * Check if store is active by store ID
     *
     * @param int $id
     * @return bool
     */
    public function isActiveStore($id)
    {
        $this->getActiveStores();

        return array_key_exists($id, $this->getActiveStores());
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Crop or add trailing slash
     *
     * @param string $url
     * @param int|null $storeId
     * @param boolean $isHomePage
     * @return string
     */
    public function trailingSlash($url, $storeId = null, $isHomePage = false)
    {
        if ($isHomePage) {
            $trailingSlash = $this->helperData->getTrailingSlashForHomePage($storeId);
        } else {
            $trailingSlash = $this->helperData->getTrailingSlash($storeId);
        }

        if ($trailingSlash == \MageWorx\SeoBase\Model\Source\AddCrop::TRAILING_SLASH_ADD) {
            $url        = rtrim($url);
            $extensions = ['rss', 'html', 'htm', 'xml', 'php'];
            if (substr($url, -1) != '/' && !in_array(substr(strrchr($url, '.'), 1), $extensions)) {
                $url .= '/';
            }
        } elseif ($trailingSlash == \MageWorx\SeoBase\Model\Source\AddCrop::TRAILING_SLASH_CROP) {
            $url = rtrim(rtrim($url), '/');
        }

        return $url;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    protected function isStoreSecure(int $storeId): bool
    {
        if ($this->_request->isSecure()) {
            return true;
        }

        $secureBaseUrl  = $this->configDataLoader->getConfigValue(Store::XML_PATH_SECURE_BASE_URL, $storeId);
        $secureFrontend = $this->configDataLoader->getConfigValue(Store::XML_PATH_SECURE_IN_FRONTEND, $storeId);

        if (!$secureBaseUrl || !$secureFrontend) {
            return false;
        }

        $uri        = UriFactory::factory($secureBaseUrl);
        $port       = $uri->getPort();
        $serverPort = $this->_request->getServer('SERVER_PORT');

        return $uri->getScheme() == 'https' && isset($serverPort) && $port == $serverPort;
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function updatePathUsingStoreView(string $url, int $storeId): string
    {
        $store = $this->storeManager->getStore($storeId);

        if ($this->isUseStoreCodeInUrl($store)) {
            $url .= $store->getCode() . '/';
        }

        return $url;
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return bool
     */
    protected function isUseStoreCodeInUrl(\Magento\Store\Api\Data\StoreInterface $store): bool
    {
        $storeId = (int)$store->getId();

        return !($store->hasDisableStoreInUrl() && $store->getDisableStoreInUrl())
            && $this->configDataLoader->getConfigValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL, $storeId);
    }
}
