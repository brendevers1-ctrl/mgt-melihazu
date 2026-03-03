<?php

namespace MageWorx\SeoAI\Plugin\Adminhtml\Category;

use MageWorx\SeoAI\Helper\Config as ModuleConfig;
use Magento\Framework\App\Request\Http as RequestHTTP;

class CategoryFormModifierPlugin
{
    protected ModuleConfig $moduleConfig;
    protected RequestHttp $requestHttp;

    public function __construct(
        ModuleConfig $moduleConfig,
        RequestHTTP $requestHttp
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->requestHttp = $requestHttp;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetMeta(\Magento\Catalog\Model\Category\DataProvider $subject, array $result): array
    {

        /**
         * You can set your own value as shown in the example
         * 'PATH' => ['Your Key' => 'Your Value']
         */

        $paths = [
            'content/children/buttons_container/arguments/data/config' => [],
            'content/children/buttons_container/children/generate_description_button/arguments/data/config' => [],
            'content/children/buttons_container/children/express_generate_description_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_seo_name_buttons_container/arguments/data/config' => [],
            'search_engine_optimization/children/generate_seo_name_buttons_container/children/generate_seo_name_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_seo_name_buttons_container/children/express_generate_seo_name_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_title_buttons_container/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_title_buttons_container/children/generate_meta_title_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_title_buttons_container/children/express_generate_meta_title_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_keywords_buttons_container/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_keywords_buttons_container/children/generate_meta_keywords_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_keywords_buttons_container/children/express_generate_meta_keywords_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_description_buttons_container/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_description_buttons_container/children/generate_meta_description_button/arguments/data/config' => [],
            'search_engine_optimization/children/generate_meta_description_buttons_container/children/express_generate_meta_description_button/arguments/data/config' => []
        ];

        foreach (array_keys($paths) as $path) {
            $setting = ['componentType' => 'container'];
            if(!empty($paths[$path])) {
                $setting = $paths[$path];
            }
            $this->setAdditionalSettingToArray($result, $path, $setting);
        }

        if (!$this->isGenerateFunctionAvailable()) {
            $result['content']['children']['buttons_container']['children']['generate_description_button']
            ['arguments']['data']['config']['visible'] = false;
            $result['content']['children']['buttons_container']['children']['express_generate_description_button']
            ['arguments']['data']['config']['visible'] = false;

            $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']['children']
            ['generate_meta_title_button']['arguments']['data']['config']['visible'] = false;
            $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']['children']
            ['express_generate_meta_title_button']['arguments']['data']['config']['visible'] = false;

            $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']['children']
            ['generate_meta_keywords_button']['arguments']['data']['config']['visible'] = false;
            $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']['children']
            ['express_generate_meta_keywords_button']['arguments']['data']['config']['visible'] = false;

            $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
            ['generate_meta_description_button']['arguments']['data']['config']['visible'] = false;
            $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
            ['express_generate_meta_description_button']['arguments']['data']['config']['visible'] = false;

            $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
            ['generate_seo_name_button']['arguments']['data']['config']['visible'] = false;
            $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
            ['express_generate_seo_name_button']['arguments']['data']['config']['visible'] = false;

            return $result;
        }

        $result['content']['children']['buttons_container']['children']['generate_description_button']
        ['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();
        $result['content']['children']['buttons_container']['children']['express_generate_description_button']
        ['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();

        $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']['children']
        ['generate_meta_title_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();
        $result['search_engine_optimization']['children']['generate_meta_title_buttons_container']['children']
        ['express_generate_meta_title_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();

        $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']['children']
        ['generate_meta_keywords_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();
        $result['search_engine_optimization']['children']['generate_meta_keywords_buttons_container']['children']
        ['express_generate_meta_keywords_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();

        $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
        ['generate_meta_description_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();
        $result['search_engine_optimization']['children']['generate_meta_description_buttons_container']['children']
        ['express_generate_meta_description_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();

        $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
        ['generate_seo_name_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();
        $result['search_engine_optimization']['children']['generate_seo_name_buttons_container']['children']
        ['express_generate_seo_name_button']['arguments']['data']['config']['disabled'] =
            !$this->moduleConfig->isAvailable();

        return $result;
    }

    /**
     * Is generation function available for current ui-form
     *
     * @return bool
     */
    protected function isGenerateFunctionAvailable(): bool
    {
        if (!$this->moduleConfig->isEnabled() || !$this->moduleConfig->isEnabledForCategory()) {
            // Disabled in config
            return false;
        }

        if ($this->requestHttp->getControllerName() === 'category' && $this->requestHttp->getActionName() === 'add') {
            // Disabled for new category form, because we are using the category ID for generation
            // @TODO: create another type of generation for new category and remove this
            return false;
        }

        return true;
    }

    private function setAdditionalSettingToArray(&$array, $path, $settings = ['componentType' => 'container'])
    {
        $keysOfPath = explode('/', $path);
        $target = &$array;
        foreach ($keysOfPath as $key) {
            if (!isset($target[$key])) {
                $target[$key] = [];
            }
            $target = &$target[$key];
        }

        $target = array_merge($settings, $target);
    }
}
