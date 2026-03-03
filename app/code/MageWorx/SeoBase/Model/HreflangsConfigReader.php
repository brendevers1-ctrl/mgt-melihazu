<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoBase\Model\Source\Hreflangs\PageTypes as PageTypesOptions;
use MageWorx\SeoBase\Model\Source\Hreflangs\LanguageCode as LanguageCodeOptions;
use MageWorx\SeoBase\Model\Source\Hreflangs\CountryCode as CountryCodeOptions;

class HreflangsConfigReader
{
    const XML_PATH_HREFLANGS_HREFLANG_SETTINGS = 'mageworx_seo/base/hreflangs/hreflang_settings';
    const XML_PATH_HREFLANGS_SCOPE             = 'mageworx_seo/base/hreflangs/scope';
    const XML_PATH_HREFLANGS_CMS_RELATION_WAY  = 'mageworx_seo/base/hreflangs/cms_relation_way';

    const XML_PATH_MAGENTO_LANGUAGE_CODE = 'general/locale/code';
    const XML_PATH_MAGENTO_COUNTRY_CODE  = 'general/country/default';

    const STORE         = 'store';
    const LANGUAGE_CODE = 'language_code';
    const COUNTRY_CODE  = 'country_code';
    const PAGES         = 'pages';
    const X_DEFAULT     = 'x_default';

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array|null
     */
    protected $hreflangSettings;

    /**
     * @var PageTypesOptions
     */
    protected $pageTypesOptions;

    /**
     * @var ConfigDataLoader
     */
    protected $configDataLoader;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        StoreManagerInterface $storeManager,
        PageTypesOptions $pageTypesOptions,
        ConfigDataLoader $configDataLoader
    ) {
        $this->scopeConfig      = $scopeConfig;
        $this->serializer       = $serializer;
        $this->storeManager     = $storeManager;
        $this->pageTypesOptions = $pageTypesOptions;
        $this->configDataLoader = $configDataLoader;
    }

    /**
     * Check if hreflangs enabled
     *
     * @param int|null $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isHreflangsEnabled(?int $storeId = null): bool
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $hreflangSettings = $this->getHreflangSettings();

        if (empty($hreflangSettings[$storeId])) {
            return false;
        }

        return true;
    }

    /**
     * Check if hreflangs enabled for pass type
     *
     * @param string|null $type
     * @param int|null $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isHreflangsEnabledFor(?string $type = null, ?int $storeId = null)
    {
        if ($storeId === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();
        }

        if (!$this->isHreflangsEnabled($storeId)) {
            return false;
        }

        $hreflangSettings = $this->getHreflangSettings();
        $pageTypes        = array_keys($this->pageTypesOptions->toArray());

        if ($type && in_array($type, $pageTypes)) {
            if (!empty($hreflangSettings[$storeId][self::PAGES])
                && is_array($hreflangSettings[$storeId][self::PAGES])
                && in_array($type, $hreflangSettings[$storeId][self::PAGES])
            ) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Retrieve hreflangs scope setting
     *
     * @return int
     */
    public function getHreflangScope(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_HREFLANGS_SCOPE);
    }

    /**
     *
     * @return array
     */
    public function getXDefaultStoreIds(): array
    {
        $xDefaultStoreIds = [];
        $hreflangSettings = $this->getHreflangSettings();
        $isGlobalScope    = ($this->getHreflangScope() == \MageWorx\SeoBase\Helper\Hreflangs::SCOPE_GLOBAL);

        foreach ($hreflangSettings as $storeId => $settings) {
            if (!empty($settings[self::X_DEFAULT])) {
                $xDefaultStoreIds[] = $storeId;

                if ($isGlobalScope) {
                    return $xDefaultStoreIds;
                }
            }
        }

        return $xDefaultStoreIds;
    }

    /**
     * Retrieve code of CMS Page relation method
     *
     * @return int
     */
    public function getCmsPageRelationWay(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_HREFLANGS_CMS_RELATION_WAY);
    }

    /**
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLanguageCode(int $storeId): string
    {
        $hreflangSettings = $this->getHreflangSettings();

        if (empty($hreflangSettings[$storeId][self::LANGUAGE_CODE])) {
            return '';
        }

        $languageCode = $hreflangSettings[$storeId][self::LANGUAGE_CODE];

        if ($languageCode === LanguageCodeOptions::USE_CONFIG) {
            return $this->getLanguageCodeFromLocale($storeId);
        }

        return $languageCode;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isCountryCodeEnabled(int $storeId): bool
    {
        $hreflangSettings = $this->getHreflangSettings();

        if (empty($hreflangSettings[$storeId][self::COUNTRY_CODE])) {
            return false;
        }

        $countryCode = $hreflangSettings[$storeId][self::COUNTRY_CODE];

        if ($countryCode === CountryCodeOptions::DO_NOT_ADD) {
            return false;
        }

        return true;
    }

    /**
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryCode(int $storeId): string
    {
        $hreflangSettings = $this->getHreflangSettings();

        if (empty($hreflangSettings[$storeId][self::COUNTRY_CODE])) {
            return '';
        }

        $countryCode = $hreflangSettings[$storeId][self::COUNTRY_CODE];

        if ($countryCode === CountryCodeOptions::DO_NOT_ADD) {
            return '';
        }

        if ($countryCode === CountryCodeOptions::USE_CONFIG) {
            return $this->getMagentoCountryCode($storeId);
        }

        return $countryCode;
    }

    /**
     * @return array
     */
    public function getHreflangSettings(): array
    {
        if (isset($this->hreflangSettings)) {
            return $this->hreflangSettings;
        }

        $this->hreflangSettings = [];
        $value                  = (string)$this->scopeConfig->getValue(self::XML_PATH_HREFLANGS_HREFLANG_SETTINGS);

        if (!empty($value)) {
            foreach ($this->serializer->unserialize($value) as $row) {
                if (!empty($row[self::STORE])) {
                    $this->hreflangSettings[$row[self::STORE]] = $row;
                }
            }
        }

        return $this->hreflangSettings;
    }

    /**
     * Retrieve language code from magento locale code
     *
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLanguageCodeFromLocale(int $storeId): string
    {
        if ($storeId != $this->storeManager->getStore()->getId()) {
            $locale = (string)$this->configDataLoader->getConfigValue(self::XML_PATH_MAGENTO_LANGUAGE_CODE, $storeId);
        }

        $locale = isset($locale) ? $locale : (string)$this->scopeConfig->getValue(
            self::XML_PATH_MAGENTO_LANGUAGE_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        list($magentoLangCode) = explode('_', $locale);

        return $magentoLangCode;
    }

    /**
     * @param int $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getMagentoCountryCode(int $storeId): string
    {
        if ($storeId != $this->storeManager->getStore()->getId()) {
            $countryCode = (string)$this->configDataLoader->getConfigValue(self::XML_PATH_MAGENTO_COUNTRY_CODE, $storeId);
        }

        return isset($countryCode) ? $countryCode : (string)$this->scopeConfig->getValue(
            self::XML_PATH_MAGENTO_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
