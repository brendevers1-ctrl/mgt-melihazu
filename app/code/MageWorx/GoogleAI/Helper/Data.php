<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\GoogleAI\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GOOGLE_API_KEY_PATH = 'mageworx_openai/main_settings/google_api_key';

    /**
     * Get Google cloud API key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getGoogleApiKey(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            static::GOOGLE_API_KEY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
