<?php

namespace Custom\OrderToSheet\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE = 'ordertosheet/general/enabled';
    const XML_PATH_WEBHOOK = 'ordertosheet/general/webhook_url';

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getWebhook()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEBHOOK, ScopeInterface::SCOPE_STORE);
    }

}
