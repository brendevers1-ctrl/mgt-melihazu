<?php

namespace MageWorx\SeoAI\Test\Unit\Plugin\Adminhtml\Category;

use Magento\Framework\App\Request\Http as RequestHttp;
use MageWorx\SeoAI\Plugin\Adminhtml\Category\CategoryFormModifierPlugin;
use MageWorx\SeoAI\Helper\Config as SeoAIHelper;
use Magento\Catalog\Model\Category\DataProvider;
use PHPUnit\Framework\TestCase;

class CategoryFormModifierPluginTest extends TestCase
{
    /**
     * @var CategoryFormModifierPlugin
     */
    protected $plugin;

    /**
     * @var SeoAIHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $seoAIHelperMock;

    /**
     * @var RequestHttp|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestHttpMock;

    protected function setUp(): void
    {
        $this->seoAIHelperMock = $this->createMock(SeoAIHelper::class);
        $this->requestHttpMock = $this->createMock(RequestHttp::class);
        $this->plugin          = new CategoryFormModifierPlugin($this->seoAIHelperMock, $this->requestHttpMock);
    }

    public function testAfterGetMetaWithModuleDisabled()
    {
        $this->seoAIHelperMock->method('isEnabled')->willReturn(false);
        $this->seoAIHelperMock->method('isEnabledForCategory')->willReturn(false);

        $dataProviderMock = $this->createMock(DataProvider::class);
        $meta             = ['some_meta_data'];

        $result = $this->plugin->afterGetMeta($dataProviderMock, $meta);

        $this->assertFalse(
            $result['content']['children']['buttons_container']['children']['generate_description_button']
            ['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['content']['children']['buttons_container']['children']['express_generate_description_button']
            ['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']
            ['children']['generate_meta_title_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']
            ['children']['express_generate_meta_title_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']
            ['children']['generate_meta_keywords_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']
            ['children']['express_generate_meta_keywords_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
            ['generate_meta_description_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
            ['express_generate_meta_description_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
            ['generate_seo_name_button']['arguments']['data']['config']['visible']
        );
        $this->assertFalse(
            $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
            ['express_generate_seo_name_button']['arguments']['data']['config']['visible']
        );
    }
}
