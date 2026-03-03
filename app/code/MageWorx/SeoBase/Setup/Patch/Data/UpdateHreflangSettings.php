<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Setup\Patch\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\Store;
use MageWorx\SeoBase\Model\HreflangsConfigReader;
use MageWorx\SeoBase\Model\Source\Hreflangs\CountryCode as CountryCodeOptions;
use MageWorx\SeoBase\Model\Source\Hreflangs\LanguageCode as LanguageCodeOptions;

class UpdateHreflangSettings implements DataPatchInterface, PatchVersionInterface
{
    /**
     * old hreflang settings paths
     */
    const XML_PATH_HREFLANGS_ENABLED                      = 'mageworx_seo/base/hreflangs/enabled';
    const XML_PATH_HREFLANGS_CATEGORY_ENABLED             = 'mageworx_seo/base/hreflangs/enabled_category';
    const XML_PATH_HREFLANGS_PRODUCT_ENABLED              = 'mageworx_seo/base/hreflangs/enabled_product';
    const XML_PATH_HREFLANGS_CMS_ENABLED                  = 'mageworx_seo/base/hreflangs/enabled_cms';
    const XML_PATH_HREFLANGS_LANDINGPAGE_ENABLED          = 'mageworx_seo/base/hreflangs/enabled_landingpage';
    const XML_PATH_HREFLANGS_USE_MAGENTO_LANGUAGE_CODE    = 'mageworx_seo/base/hreflangs/use_magento_lang_code';
    const XML_PATH_HREFLANGS_LANGUAGE_CODE                = 'mageworx_seo/base/hreflangs/lang_code';
    const XML_PATH_HREFLANGS_COUNTRY_CODE_ENABLED         = 'mageworx_seo/base/hreflangs/country_code_enabled';
    const XML_PATH_HREFLANGS_USE_MAGENTO_COUNTRY_CODE     = 'mageworx_seo/base/hreflangs/use_magento_country_code';
    const XML_PATH_HREFLANGS_COUNTRY_CODE                 = 'mageworx_seo/base/hreflangs/country_code';
    const XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE  = 'mageworx_seo/base/hreflangs/x_default_global';
    const XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE = 'mageworx_seo/base/hreflangs/x_default_website';

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Json $serializer
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->serializer      = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $oldPaths = [
            self::XML_PATH_HREFLANGS_ENABLED,
            self::XML_PATH_HREFLANGS_CATEGORY_ENABLED,
            self::XML_PATH_HREFLANGS_PRODUCT_ENABLED,
            self::XML_PATH_HREFLANGS_CMS_ENABLED,
            self::XML_PATH_HREFLANGS_LANDINGPAGE_ENABLED,
            self::XML_PATH_HREFLANGS_USE_MAGENTO_LANGUAGE_CODE,
            self::XML_PATH_HREFLANGS_LANGUAGE_CODE,
            self::XML_PATH_HREFLANGS_COUNTRY_CODE_ENABLED,
            self::XML_PATH_HREFLANGS_USE_MAGENTO_COUNTRY_CODE,
            self::XML_PATH_HREFLANGS_COUNTRY_CODE,
            self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE,
            self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE
        ];

        $paths      = array_merge($oldPaths, [HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE]);
        $connection = $this->moduleDataSetup->getConnection();
        $select     = $connection->select();
        $select
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['scope', 'scope_id', 'path', 'value'])
            ->where('path IN (?)', $paths);

        $groupedConfigData = [];

        foreach ($connection->fetchAll($select) as $row) {
            $groupedConfigData[$row['path']][$row['scope']][$row['scope_id']] = $row['value'];
        }

        if (empty($groupedConfigData)) {
            return;
        }

        if (!empty($groupedConfigData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE])
            && count($groupedConfigData) === 1
        ) {
            return;
        }

        $groupedStoreIds  = $this->getStoreIdsGroupedByWebsite();
        $configData       = $this->prepareHreflangConfigData($groupedConfigData, $groupedStoreIds);
        $hreflangSettings = [];

        foreach ($groupedStoreIds as $storeIds) {
            foreach ($storeIds as $storeId) {
                $data    = [];
                $storeId = (int)$storeId;

                if (empty($configData[self::XML_PATH_HREFLANGS_ENABLED][$storeId])) {
                    continue;
                }

                $data[HreflangsConfigReader::STORE] = $storeId;

                $this->addPagesToStoreHreflangSettingsData($storeId, $data, $configData);
                $this->addLanguageCodeToStoreHreflangSettingsData($storeId, $data, $configData);
                $this->addCountryCodeToStoreHreflangSettingsData($storeId, $data, $configData);
                $this->addXDefaultToStoreHreflangSettingsData($storeId, $data, $configData);

                $hreflangSettings[$storeId] = $data;
            }
        }

        if (!empty($hreflangSettings)) {
            $hreflangSettings = $this->serializer->serialize($hreflangSettings);

            $connection->insertOnDuplicate(
                $this->moduleDataSetup->getTable('core_config_data'),
                [
                    'scope'    => 'default',
                    'scope_id' => 0,
                    'path'     => HreflangsConfigReader::XML_PATH_HREFLANGS_HREFLANG_SETTINGS,
                    'value'    => $hreflangSettings
                ]
            );
        }

        $connection->delete($this->moduleDataSetup->getTable('core_config_data'), ['path IN (?)' => $oldPaths]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return '2.2.1';
    }

    /**
     * @param array $groupedConfigData
     * @param array $groupedStoreIds
     * @return array
     */
    private function prepareHreflangConfigData(array $groupedConfigData, array $groupedStoreIds): array
    {
        $configData     = [];
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if (!empty($groupedConfigData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE])) {
            $scope = $groupedConfigData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE]['default'][$defaultStoreId];

            $configData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE][$defaultStoreId] = $scope;

            unset($groupedConfigData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE]);
        }

        if (!empty($groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE])) {
            $xDefault = (string)$groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE]['default'][0];

            $configData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE][$defaultStoreId] = $xDefault;

            unset($groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE]);
        }

        if (!empty($groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE])) {
            $xDefault = (string)$groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE]['default'][0];

            $configData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE][$defaultStoreId] = $xDefault;

            unset($groupedConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE]);
        }

        foreach ($groupedConfigData as $path => $groupedValues) {
            foreach ($groupedStoreIds as $websiteId => $storeIds) {
                foreach ($storeIds as $storeId) {
                    if (!empty($groupedValues['stores']) && array_key_exists($storeId, $groupedValues['stores'])) {
                        $configData[$path][$storeId] = $groupedValues['stores'][$storeId];
                        continue;
                    }

                    if (!empty($groupedValues['websites'])
                        && array_key_exists($websiteId, $groupedValues['websites'])
                    ) {
                        $configData[$path][$storeId] = $groupedValues['websites'][$websiteId];
                        continue;
                    }

                    if (!empty($groupedValues['default'])) {
                        $configData[$path][$storeId] = $groupedValues['default'][$defaultStoreId];
                    }
                }
            }
        }

        return $configData;
    }

    /**
     * @return array
     */
    private function getStoreIdsGroupedByWebsite(): array
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select     = $connection->select();
        $select
            ->from($this->moduleDataSetup->getTable('store'), ['store_id', 'website_id'])
            ->where('store_id != ?', Store::DEFAULT_STORE_ID);

        $storeIds = [];

        foreach ($connection->fetchAll($select) as $row) {
            $storeIds[$row['website_id']][] = $row['store_id'];
        }

        return $storeIds;
    }

    /**
     * @param int $storeId
     * @param array $data
     * @param array $oldConfigData
     */
    private function addPagesToStoreHreflangSettingsData(int $storeId, array &$data, array $oldConfigData)
    {
        if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_CATEGORY_ENABLED][$storeId])) {
            $data[HreflangsConfigReader::PAGES][] = 'category';
        }

        if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_PRODUCT_ENABLED][$storeId])) {
            $data[HreflangsConfigReader::PAGES][] = 'product';
        }

        if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_CMS_ENABLED][$storeId])) {
            $data[HreflangsConfigReader::PAGES][] = 'cms';
        }

        if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_LANDINGPAGE_ENABLED][$storeId])) {
            $data[HreflangsConfigReader::PAGES][] = 'landingpage';
        }
    }

    /**
     * @param int $storeId
     * @param array $data
     * @param array $oldConfigData
     */
    private function addLanguageCodeToStoreHreflangSettingsData(int $storeId, array &$data, array $oldConfigData)
    {
        if (empty($oldConfigData[self::XML_PATH_HREFLANGS_USE_MAGENTO_LANGUAGE_CODE][$storeId])) {
            $languageCode = '';

            if (isset($oldConfigData[self::XML_PATH_HREFLANGS_LANGUAGE_CODE][$storeId])) {
                $languageCode = $oldConfigData[self::XML_PATH_HREFLANGS_LANGUAGE_CODE][$storeId];
            }

            $data[HreflangsConfigReader::LANGUAGE_CODE] = $languageCode;
        } else {
            $data[HreflangsConfigReader::LANGUAGE_CODE] = LanguageCodeOptions::USE_CONFIG;
        }
    }

    /**
     * @param int $storeId
     * @param array $data
     * @param array $oldConfigData
     */
    private function addCountryCodeToStoreHreflangSettingsData(int $storeId, array &$data, array $oldConfigData)
    {
        if (empty($oldConfigData[self::XML_PATH_HREFLANGS_COUNTRY_CODE_ENABLED][$storeId])) {
            $data[HreflangsConfigReader::COUNTRY_CODE] = CountryCodeOptions::DO_NOT_ADD;
        } else {
            if (empty($oldConfigData[self::XML_PATH_HREFLANGS_USE_MAGENTO_COUNTRY_CODE][$storeId])) {
                $countryCode = '';

                if (isset($oldConfigData[self::XML_PATH_HREFLANGS_COUNTRY_CODE][$storeId])) {
                    $countryCode = $oldConfigData[self::XML_PATH_HREFLANGS_COUNTRY_CODE][$storeId];
                }

                $data[HreflangsConfigReader::COUNTRY_CODE] = $countryCode;
            } else {
                $data[HreflangsConfigReader::COUNTRY_CODE] = CountryCodeOptions::USE_CONFIG;
            }
        }
    }

    /**
     * @param int $storeId
     * @param array $data
     * @param array $oldConfigData
     */
    private function addXDefaultToStoreHreflangSettingsData(int $storeId, array &$data, array $oldConfigData)
    {
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if (!empty($oldConfigData[HreflangsConfigReader::XML_PATH_HREFLANGS_SCOPE][$defaultStoreId])) {
            if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE][$defaultStoreId])
            ) {
                $storeIds = $oldConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_WEBSITE_SCOPE][$defaultStoreId];
                $storeIds = explode(',', $storeIds);

                if (in_array($storeId, $storeIds)) {
                    $data[HreflangsConfigReader::X_DEFAULT] = 1;
                }
            }
        } else {
            if (!empty($oldConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE][$defaultStoreId])
                && $oldConfigData[self::XML_PATH_HREFLANGS_XDEFAULT_STORE_GLOBAL_SCOPE][$defaultStoreId] == $storeId
            ) {
                $data[HreflangsConfigReader::X_DEFAULT] = 1;
            }
        }
    }
}
