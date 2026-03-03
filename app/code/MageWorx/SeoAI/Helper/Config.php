<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAI\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    public function getValue(string $path, ?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check is generate enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'mageworx_seo/mageworx_seoai/is_enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is generate enabled for category
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForCategory(?int $storeId = null): bool
    {
        $enabledOn = (string)$this->scopeConfig->getValue(
            'mageworx_seo/mageworx_seoai/enabled_on',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $enabledOnArray = explode(',', $enabledOn);

        return in_array('category', $enabledOnArray);
    }

    /**
     * Check is generate enabled for product
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledForProduct(?int $storeId = null): bool
    {
        $enabledOn = (string)$this->scopeConfig->getValue(
            'mageworx_seo/mageworx_seoai/enabled_on',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $enabledOnArray = explode(',', $enabledOn);

        return in_array('product', $enabledOnArray);
    }

    /**
     * Is Generation available:
     * - if api key is not set the generation process will be unavailable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAvailable(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
                'mageworx_openai/main_settings/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) || $this->scopeConfig->isSetFlag(
                'mageworx_openai/main_settings/google_api_key',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
}
