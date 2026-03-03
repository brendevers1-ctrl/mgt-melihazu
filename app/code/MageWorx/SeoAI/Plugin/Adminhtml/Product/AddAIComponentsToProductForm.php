<?php

namespace MageWorx\SeoAI\Plugin\Adminhtml\Product;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Request\Http as RequestHTTP;
use MageWorx\SeoAI\Helper\Config as ModuleConfig;

class AddAIComponentsToProductForm
{
    protected UrlInterface $urlBuilder;
    protected ModuleConfig $moduleConfig;
    protected RequestHttp  $requestHttp;

    public function __construct(
        ModuleConfig $moduleConfig,
        UrlInterface $urlBuilder,
        RequestHTTP  $requestHttp
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->urlBuilder   = $urlBuilder;
        $this->requestHttp  = $requestHttp;
    }

    protected function getGenerateButtonFor(string $type, int $sortOrder): array
    {
        return [
            'dataScope'          => 'mageworx_seo',
            'displayAsLink'      => false,
            'formElement'        => 'container',
            'componentType'      => 'container',
            'component'          => 'Magento_Ui/js/form/components/button',
            'template'           => 'ui/form/components/button/container',
            'actions'            => [
                [
                    'targetName' => 'product_form.product_form.seo_generate_modal',
                    'actionName' => 'toggleModal',
                ],
                [
                    'targetName' => 'product_form.product_form.seo_generate_modal.seo_generate_system.message_type',
                    'actionName' => 'setValue',
                    'params'     => [$type]
                ],
                [
                    'targetName' => 'product_form.product_form.seo_generate_modal.seo_generate_extended_settings.buttons_container.get_request_data_button',
                    'actionName' => 'sendRequestAction'
                ],
                [
                    'targetName'    => '${ $.name }',
                    '__disableTmpl' => ['targetName' => false],
                    'actionName'    => 'disabled',
                    'params'        => [false]
                ]
            ],
            'title'              => __('AI-Powered Generator'),
            'additionalForGroup' => true,
            'provider'           => false,
            'source'             => 'product_details',
            'sortOrder'          => $sortOrder,
            'additionalClasses'  => 'admin__field-small mw-seo-ai-button-generate',
            'disabled'           => !$this->moduleConfig->isAvailable()
        ];
    }

    protected function getExpressGenerateButtonFor(string $type, int $sortOrder, ?bool $isForPageBuilder = false): array
    {
        return [
            'dataScope'          => 'mageworx_seo',
            'displayAsLink'      => false,
            'formElement'        => 'container',
            'componentType'      => 'container',
            'component'          => $isForPageBuilder !== true ?
                'MageWorx_SeoAI/js/product_form/element/buttons/express-generate-button' :
                'MageWorx_SeoAI/js/product_form/element/buttons/express-generate-button-pagebuilder',
            'template'           => 'ui/form/components/button/container',
            'actions'            => [
                [
                    'targetName'    => '${ $.name }',
                    '__disableTmpl' => ['targetName' => false],
                    'actionName'    => 'sendRequestAction',
                    'params'        => [$type]
                ]
            ],
            'title'              => __('AI Express Generator'),
            'additionalForGroup' => true,
            'provider'           => false,
            'source'             => 'product_details',
            'sortOrder'          => $sortOrder,
            'additionalClasses'  => 'admin__field-small mw-seo-ai-button-generate',
            'buttonClasses'      => 'action-primary',
            'ajaxUrl'            => $this->urlBuilder->getUrl('mageworx_seoai/product/generate'),
            'disabled'           => !$this->moduleConfig->isAvailable()
        ];
    }

    protected function getImproveButtonFor(string $type, int $sortOrder, ?bool $isForPageBuilder = false): array
    {
        return [
            'dataScope'          => 'mageworx_seo',
            'displayAsLink'      => false,
            'formElement'        => 'container',
            'componentType'      => 'container',
            'component'          => $isForPageBuilder !== true ?
                'MageWorx_SeoAI/js/product_form/element/buttons/express-generate-button' :
                'MageWorx_SeoAI/js/product_form/element/buttons/express-generate-button-pagebuilder',
            'template'           => 'ui/form/components/button/container',
            'actions'            => [
                [
                    'targetName'    => '${ $.name }',
                    '__disableTmpl' => ['targetName' => false],
                    'actionName'    => 'sendRequestAction',
                    'params'        => [$type]
                ]
            ],
            'title'              => __('Improve with AI'),
            'additionalForGroup' => true,
            'provider'           => false,
            'source'             => 'product_details',
            'sortOrder'          => $sortOrder,
            'additionalClasses'  => 'admin__field-small mw-seo-ai-button-generate-express',
            'buttonClasses'      => 'action-primary',
            'ajaxUrl'            => $this->urlBuilder->getUrl('mageworx_seoai/product/generate'),
            'disabled'           => !$this->moduleConfig->isAvailable()
        ];
    }

    /**
     * @param AbstractModifier $subject
     * @param array $result
     * @return array
     */
    public function afterModifyMeta(
        AbstractModifier $subject,
        array            $result
    ): array {
        if (!$this->isGenerateFunctionAvailable()) {
            unset($result['seo_generate_modal']);

            return $result;
        }

        if (isset($result['content']['children']['container_short_description'])) {
            $result['content']['children']['container_short_description']['children']
            ['generate_short_description_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_short_description', 30);

            $result['content']['children']['container_short_description']['children']
            ['express_generate_short_description_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_short_description', 20);

            $result['content']['children']['container_short_description']['children']
            ['improve_short_description_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_short_description', 15);
        }

        if (isset($result['content']['children']['container_description'])) {
            $result['content']['children']['container_description']['children']
            ['generate_description_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_description', 30);

            $result['content']['children']['container_description']['children']
            ['express_generate_description_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_description', 20, true);

            $result['content']['children']['container_description']['children']
            ['improve_description_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_description', 15);
        }

        // product_form.product_form.search-engine-optimization.container_meta_title.meta_title
        if (isset($result['search-engine-optimization']['children']['container_meta_title'])) {
            $result['search-engine-optimization']['children']['container_meta_title']['children']
            ['generate_meta_title_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_meta_title', 41);

            $result['search-engine-optimization']['children']['container_meta_title']['children']
            ['express_generate_meta_title_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_meta_title', 41);

            $result['search-engine-optimization']['children']['container_meta_title']['children']
            ['improve_meta_title_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_meta_title', 41);
        }

        // product_form.product_form.search-engine-optimization.container_product_seo_name.product_seo_name
        if (isset($result['search-engine-optimization']['children']['container_product_seo_name'])) {
            $result['search-engine-optimization']['children']['container_product_seo_name']['children']
            ['generate_seo_name_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_seo_name', 110);

            $result['search-engine-optimization']['children']['container_product_seo_name']['children']
            ['express_generate_seo_name_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_seo_name', 110);

            $result['search-engine-optimization']['children']['container_product_seo_name']['children']
            ['improve_seo_name_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_seo_name', 110);
        }

        // product_form.product_form.search-engine-optimization.container_meta_keyword.meta_keyword
        if (isset($result['search-engine-optimization']['children']['container_meta_keyword'])) {
            $result['search-engine-optimization']['children']['container_meta_keyword']['children']
            ['generate_meta_keywords_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_meta_keyword', 41);

            $result['search-engine-optimization']['children']['container_meta_keyword']['children']
            ['express_generate_meta_keywords_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_meta_keyword', 41);

            $result['search-engine-optimization']['children']['container_meta_keyword']['children']
            ['improve_meta_keywords_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_meta_keyword', 41);
        }

        // product_form.product_form.search-engine-optimization.container_meta_description.meta_description
        if (isset($result['search-engine-optimization']['children']['container_meta_description'])) {
            $result['search-engine-optimization']['children']['container_meta_description']['children']
            ['generate_meta_description_button']['arguments']['data']['config'] =
                $this->getGenerateButtonFor('product_meta_description', 41);

            $result['search-engine-optimization']['children']['container_meta_description']['children']
            ['express_generate_meta_description_button']['arguments']['data']['config'] =
                $this->getExpressGenerateButtonFor('product_meta_description', 41);

            $result['search-engine-optimization']['children']['container_meta_description']['children']
            ['improve_meta_description_button']['arguments']['data']['config'] =
                $this->getImproveButtonFor('product_improve_meta_description', 41);
        }

        return $result;
    }

    /**
     * Is generation function available for current ui-form
     *
     * @return bool
     */
    protected function isGenerateFunctionAvailable(): bool
    {
        if (!$this->moduleConfig->isEnabled() || !$this->moduleConfig->isEnabledForProduct()) {
            // Disabled in config
            return false;
        }

        if ($this->requestHttp->getControllerName() === 'product' && $this->requestHttp->getActionName() === 'new') {
            // Disabled for new product form, because we are using the product ID for generation
            // @TODO: create another type of generation for new product and remove this
            return false;
        }

        return true;
    }
}
