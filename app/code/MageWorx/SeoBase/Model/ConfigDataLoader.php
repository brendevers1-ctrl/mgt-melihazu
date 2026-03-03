<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace MageWorx\SeoBase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

class ConfigDataLoader
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array|null
     */
    protected $data;

    /**
     * @var array
     */
    protected $paths = [
        \MageWorx\SeoBase\Model\HreflangsConfigReader::XML_PATH_MAGENTO_LANGUAGE_CODE,
        \MageWorx\SeoBase\Model\HreflangsConfigReader::XML_PATH_MAGENTO_COUNTRY_CODE,
        \MageWorx\SeoBase\Helper\Data::XML_PATH_PRODUCT_CANONICAL_URL_TYPE,
        \MageWorx\SeoBase\Helper\Data::XML_PATH_TRAILING_SLASH_FOR_HOME,
        \MageWorx\SeoBase\Helper\Data::XML_PATH_TRAILING_SLASH,
        \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
        Store::XML_PATH_SECURE_BASE_URL,
        Store::XML_PATH_SECURE_IN_FRONTEND,
        Store::XML_PATH_SECURE_BASE_LINK_URL,
        Store::XML_PATH_UNSECURE_BASE_URL,
        Store::XML_PATH_UNSECURE_BASE_LINK_URL,
        Store::XML_PATH_STORE_IN_URL
    ];

    /**
     * ConfigDataLoader constructor.
     *
     * @param ResourceConnection $resource
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ResourceConnection $resource, ScopeConfigInterface $scopeConfig)
    {
        $this->resource    = $resource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $path
     * @param int $storeId
     * @return string|null
     */
    public function getConfigValue(string $path, int $storeId): ?string
    {
        $value = (string)$this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);
        if ($value) {
            return $value;
        }

        if (!isset($this->data)) {
            $this->loadConfigData();
        }

        if (isset($this->data[$path][$storeId])) {
            return (string)$this->data[$path][$storeId];
        }

        if ($path == Store::XML_PATH_SECURE_BASE_LINK_URL
            && isset($this->data[Store::XML_PATH_SECURE_BASE_URL][$storeId])
        ) {
            return (string)$this->data[Store::XML_PATH_SECURE_BASE_URL][$storeId];
        }

        if ($path == Store::XML_PATH_UNSECURE_BASE_LINK_URL
            && isset($this->data[Store::XML_PATH_UNSECURE_BASE_URL][$storeId])) {
            return (string)$this->data[Store::XML_PATH_UNSECURE_BASE_URL][$storeId];
        }

        if (isset($this->data[$path][Store::DEFAULT_STORE_ID])) {
            return (string)$this->data[$path][Store::DEFAULT_STORE_ID];
        }

        return null;
    }

    /**
     * @return void
     */
    protected function loadConfigData(): void
    {
        $this->data = [];

        $connection = $this->resource->getConnection();
        $select     = $connection->select();
        $select
            ->from($this->resource->getTableName('core_config_data'), ['scope', 'scope_id', 'path', 'value'])
            ->where('path IN (?)', $this->paths)
            ->where('scope IN (?)', ['stores', 'websites']);

        $groupedConfigData = [];

        foreach ($connection->fetchAll($select) as $row) {
            $groupedConfigData[$row['path']][$row['scope']][$row['scope_id']] = $row['value'];
        }

        $groupedStoreIds = $this->getStoreIdsGroupedByWebsite();

        foreach ($groupedConfigData as $path => $groupedValues) {
            foreach ($groupedStoreIds as $websiteId => $storeIds) {
                foreach ($storeIds as $storeId) {
                    if (!empty($groupedValues['stores']) && array_key_exists($storeId, $groupedValues['stores'])) {
                        $this->data[$path][$storeId] = $groupedValues['stores'][$storeId];
                        continue;
                    }

                    if (!empty($groupedValues['websites'])
                        && array_key_exists($websiteId, $groupedValues['websites'])
                    ) {
                        $this->data[$path][$storeId] = $groupedValues['websites'][$websiteId];
                    }
                }
            }
        }

        foreach ($this->paths as $path) {
            $defaultValue = $this->scopeConfig->getValue($path);

            $this->data[$path][Store::DEFAULT_STORE_ID] = isset($defaultValue) ? (string)$defaultValue : null;
        }
    }

    /**
     * @return array
     */
    protected function getStoreIdsGroupedByWebsite(): array
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select();
        $select
            ->from($this->resource->getTableName('store'), ['website_id', 'store_id'])
            ->where('store_id != ?', Store::DEFAULT_STORE_ID);

        $storeIds = [];

        foreach ($connection->fetchAll($select) as $row) {
            $storeIds[$row['website_id']][] = $row['store_id'];
        }

        return $storeIds;
    }
}
