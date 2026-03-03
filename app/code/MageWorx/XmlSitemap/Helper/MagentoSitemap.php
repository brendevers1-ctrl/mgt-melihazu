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

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class MagentoSitemap extends AbstractHelper
{
    /**
     * Error email template
     */
    const XML_PATH_ERROR_TEMPLATE = 'sitemap/generate/error_email_template';

    /**
     * Enable/disable
     */
    const XML_PATH_GENERATION_ENABLED = 'sitemap/generate/enabled';

    /**
     * 'Send error emails to'
     */
    const XML_PATH_ERROR_RECIPIENT = 'sitemap/generate/error_email';

    /**
     * Error email identity
     */
    const XML_PATH_ERROR_IDENTITY = 'sitemap/generate/error_email_identity';

    /**
     * Config path to sitemap valid paths
     */
    const XML_PATH_SITEMAP_VALID_PATHS = 'sitemap/file/valid_paths';

    /**
     * Config path to valid file paths
     */
    const XML_PATH_PUBLIC_FILES_VALID_PATHS = 'general/file/public_files_valid_paths';

    /**
     * Get error email template
     *
     * @param int $storeId
     * @return string
     */
    public function getErrorEmailTemplate($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ERROR_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Enable/disable
     *
     * @param int $storeId
     * @return bool
     */
    public function isGenerationEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_GENERATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * 'Send error emails to'
     *
     * @param int $storeId
     * @return string
     */
    public function getErrorRecipient($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * 'Send error emails to'
     *
     * @param int $storeId
     * @return string
     */
    public function getErrorIdentity($storeId = null)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ERROR_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getValidPaths()
    {
        return array_merge(
            $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_VALID_PATHS, ScopeInterface::SCOPE_STORE),
            $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_FILES_VALID_PATHS, ScopeInterface::SCOPE_STORE)
        );
    }
}
