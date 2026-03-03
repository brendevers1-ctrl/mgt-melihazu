<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * SEO Markup Breadcrumbs Helper
 */
class Breadcrumbs extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path for breadcrumbs setting
     */
    const XML_PATH_BREADCRUMBS_ENABLED = 'mageworx_seo/markup/breadcrumbs/rs_enabled';

    /**
     * Check if enabled in the breadcrumbs
     *
     * @param int|null $storeId
     * @return boolean
     */
    public function isRsEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_BREADCRUMBS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
