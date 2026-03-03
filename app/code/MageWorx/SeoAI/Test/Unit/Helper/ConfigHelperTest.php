<?php

namespace MageWorx\SeoAI\Test\Unit\Helper;

use MageWorx\SeoAI\Helper\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class ConfigHelperTest extends TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->config = new Config($contextMock);
    }

    public function testGetValue()
    {
        $this->scopeConfigMock->expects($this->once())
                              ->method('getValue')
                              ->with('test_path', ScopeInterface::SCOPE_STORE, 1)
                              ->willReturn('test_value');

        $this->assertEquals('test_value', $this->config->getValue('test_path', 1));
    }

    public function testIsEnabled()
    {
        $this->scopeConfigMock->expects($this->once())
                              ->method('isSetFlag')
                              ->with('mageworx_seo/mageworx_seoai/is_enabled', ScopeInterface::SCOPE_STORE, 1)
                              ->willReturn(true);

        $this->assertTrue($this->config->isEnabled(1));
    }

    public function testIsEnabledForCategory()
    {
        $this->scopeConfigMock->expects($this->once())
                              ->method('getValue')
                              ->with('mageworx_seo/mageworx_seoai/enabled_on', ScopeInterface::SCOPE_STORE, 1)
                              ->willReturn('category,product');

        $this->assertTrue($this->config->isEnabledForCategory(1));
    }

    public function testIsEnabledForProduct()
    {
        $this->scopeConfigMock->expects($this->once())
                              ->method('getValue')
                              ->with('mageworx_seo/mageworx_seoai/enabled_on', ScopeInterface::SCOPE_STORE, 1)
                              ->willReturn('category,product');

        $this->assertTrue($this->config->isEnabledForProduct(1));
    }
}
