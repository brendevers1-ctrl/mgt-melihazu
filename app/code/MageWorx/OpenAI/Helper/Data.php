<?php

namespace MageWorx\OpenAI\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_KEY_PATH = 'mageworx_openai/main_settings/api_key';

    public function getApiKey(?int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            static::API_KEY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
