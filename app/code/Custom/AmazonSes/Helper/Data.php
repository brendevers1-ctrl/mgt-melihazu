<?php
namespace Custom\AmazonSes\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    private const XML_PATH = 'amazonses/general/';


    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH . 'enabled', ScopeInterface::SCOPE_STORE);
    }


    public function getKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'access_key', ScopeInterface::SCOPE_STORE);
    }


    public function getSecret(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'secret_key', ScopeInterface::SCOPE_STORE);
    }


    public function getRegion(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'region', ScopeInterface::SCOPE_STORE);
    }


    public function getFrom(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH . 'from_email', ScopeInterface::SCOPE_STORE);
    }
}
