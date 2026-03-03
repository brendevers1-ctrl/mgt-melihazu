<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoXTemplates\Block\Adminhtml\Template\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use MageWorx\SeoXTemplates\Model\CodeTypeFactory;
use MageWorx\SeoXTemplates\Model\Template\Source\IsUseCron as IsUseCronOptions;
use MageWorx\SeoXTemplates\Model\Template\Source\Scope     as ScopeOptions;
use MageWorx\SeoXTemplates\Model\Template\Product\Source\AssignType as AssignTypeOptions;
use MageWorx\SeoXTemplates\Model\Template\Product\Source\Type as TypeOptions;
use MageWorx\SeoXTemplates\Helper\Comment\Product as HelperComment;
use MageWorx\SeoXTemplates\Helper\Store as HelperStore;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;

class Main extends GenericForm implements TabInterface
{
    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;

    /**
     * @var ScopeOptions
     */
    protected $scopeOptions;

    /**
     * @var IsUseCronOptions
     */
    protected $isUseCronOptions;

    /**
     * @var AssignTypeOptions
     */
    protected $assignTypeOptions;

    /**
     * @var TypeOptions
     */
    protected $typeOptions;

    /**
     * @var HelperComment
     */
    protected $helperComment;

    /**
     * @var HelperStore
     */
    protected $helperStore;

    /**
     * @var WysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     * @var CodeTypeFactory
     */
    protected $codeTypeFactory;

    /**
     * Main constructor.
     *
     * @param Store $store
     * @param TypeOptions $typeOptions
     * @param IsUseCronOptions $isUseCronOptions
     * @param ScopeOptions $scopeOptions
     * @param AssignTypeOptions $assignTypeOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param HelperComment $helperComment
     * @param HelperStore $helperStore
     * @param WysiwygConfig $wysiwygConfig
     * @param CodeTypeFactory $codeTypeFactory
     * @param array $data
     */
    public function __construct(
        Store $store,
        TypeOptions $typeOptions,
        IsUseCronOptions $isUseCronOptions,
        ScopeOptions $scopeOptions,
        AssignTypeOptions $assignTypeOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HelperComment $helperComment,
        HelperStore $helperStore,
        WysiwygConfig $wysiwygConfig,
        CodeTypeFactory $codeTypeFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->registry              = $registry;
        $this->store                 = $store;
        $this->scopeOptions          = $scopeOptions;
        $this->isUseCronOptions      = $isUseCronOptions;
        $this->assignTypeOptions     = $assignTypeOptions;
        $this->typeOptions           = $typeOptions;
        $this->helperComment         = $helperComment;
        $this->helperStore           = $helperStore;
        $this->wysiwygConfig         = $wysiwygConfig;
        $this->codeTypeFactory       = $codeTypeFactory;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\SeoXTemplates\Model\Template\Product $template */
        $template = $this->registry->registry('mageworx_seoxtemplates_template');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('template_');
        $form->setFieldNameSuffix('template');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => $this->getLegendText(),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($template->getId()) {
            $fieldset->addField(
                'template_id',
                'hidden',
                ['name' => 'template_id']
            );
        }

        $fieldset->addField(
            'type_id',
            'hidden',
            [
                'name'      => 'type_id',
                'value'     => $this->getTemplateTypeId()
            ]
        );

        $fieldset->addField(
            'store_id',
            'hidden',
            [
                'name'      => 'store_id',
                'value'     => $this->getTemplateStoreId()
            ]
        );

        $fieldset->addField(
            'is_single_store_mode',
            'hidden',
            [
                'name'  => 'is_single_store_mode',
                'value' => $this->getIsSingleStoreMode()
            ]
        );

        $fieldset->addField('assign_type', 'radios', [
          'label'              => 'Assign Type',
          'name'               => 'assign_type',
          'values'             => $this->getAssignTypes(),
          'disabled'           => false,
          'readonly'           => false,
          'after_element_html' => '<br><small>' . __('See the changed tab in the tab list') . '</small>',
        ]);

        $fieldset->addField(
            'name',
            'text',
            [
                'name'      => 'name',
                'label'     => __('Name'),
                'title'     => __('Name'),
                'required'  => true,
            ]
        );

        $this->addCodeField($template, $fieldset);

        $fieldset->addField(
            'scope',
            'select',
            [
                'name'      => 'scope',
                'label'     => __('Apply For'),
                'title'     => __('Apply For'),
                'required'  => true,
                'options'   => $this->scopeOptions->toArray()
            ]
        );

         $fieldset->addField(
             'is_use_cron',
             'select',
             [
                'name'      => 'is_use_cron',
                'label'     => __('Apply By Cron'),
                'title'     => __('Apply By Cron'),
                'required'  => true,
                'options'   => $this->isUseCronOptions->toArray(),
                'after_element_html' => $this->helperComment->getComments($this->getTemplateTypeId())
             ]
         );

        $templateData = $this->_session->getData('mageworx_seoxtemplates_template_data', true);

        if ($templateData) {
            $template->addData($templateData);
        } else {
            if (!$template->getId()) {
                $template->addData($template->getDefaultValuesForEdit());
            }
        }

        $form->addValues($template->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Product Templates');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve legend text
     *
     * @return string
     */
    protected function getLegendText()
    {
        $storeId        = $this->getTemplateStoreId();
        $storeViewName  = $this->store->getStoreName($storeId);

        $templateTypes  = $this->typeOptions->toArray();
        $templateName   = $templateTypes[$this->getTemplateTypeId()];

        if (!$storeId && $this->getIsSingleStoreMode()) {
            return __('Edit "%1" Template for Single-Store Mode', $templateName);
        }

        if (!$storeId) {
            return __('Edit "%1" Template for All Store Views', $templateName);
        }

        return __('Edit "%1" Template for "%2" Store View', $templateName, $storeViewName);
    }

    /**
     *
     * @return int
     */
    protected function getTemplateStoreId()
    {
        return $this->getProductTemplate()->getStoreId();
    }

    /**
     *
     * @return int
     */
    protected function getTemplateTypeId()
    {
        return $this->getProductTemplate()->getTypeId();
    }

    /**
     *
     * @return int
     */
    protected function getIsSingleStoreMode()
    {
        if (is_null($this->getRequest()->getParam('is_new'))) {
            return $this->getProductTemplate()->getIsSingleStoreMode();
        }

        if ($this->_storeManager->isSingleStoreMode()) {
            return HelperStore::SINGLE_STORE_MODE_ENABLED;
        }

        return HelperStore::SINGLE_STORE_MODE_DISABLED;
    }

    /**
     *
     * @return \MageWorx\SeoXTemplates\Model\Template\Product
     */
    protected function getProductTemplate()
    {
        return $this->registry->registry('mageworx_seoxtemplates_template');
    }

    /**
     * Retrieve filtered by same template type assign options
     *
     * @return array
     */
    protected function getAssignTypes()
    {
        $options = $this->assignTypeOptions->toOptionArray();

        if ($this->getProductTemplate()->getDuplicateTemplateAssignedForAll()) {
            foreach ($options as $key => $option) {
                if ($option['value'] == \MageWorx\SeoXTemplates\Model\Template\Product::ASSIGN_ALL_ITEMS) {
                    unset($options[$key]);
                }
            }
        }
        return $options;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getSwitcherScript();
    }

    /**
     * Add JS tab switcher in according template's assigned type
     * Don't use element id - different between magento 2.3 and 2.4
     *
     * @return string
     */
    protected function getSwitcherScript()
    {
        return "<script>
             require([
            'jquery'
        ], function($) {
            $('#template_assign_type1').on('change', function() {
                if ($('#template_assign_type1:checked')) {
                    $('li[aria-labelledby=template_product_tabs_products]').hide();
                    $('li[aria-labelledby=template_product_tabs_attributeset]').hide();
                }
            });

            $('#template_assign_type2').on('change', function() {
                if ($('#template_assign_type2:checked')) {
                    $('li[aria-labelledby=template_product_tabs_products]').show();
                    $('li[aria-labelledby=template_product_tabs_attributeset]').hide();
                }
            });

            $('#template_assign_type3').on('change', function() {
                if ($('#template_assign_type3:checked')) {
                    $('li[aria-labelledby=template_product_tabs_products]').hide();
                    $('li[aria-labelledby=template_product_tabs_attributeset]').show();
                }
            });
        });
        </script>";
    }

    /**
     * @param \MageWorx\SeoXTemplates\Model\Template\Product $template
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    protected function addCodeField($template, $fieldset)
    {
        $fieldType = $this->codeTypeFactory->getFieldTypeByCode($this->getTemplateTypeId());

        if ($fieldType === 'html') {
            $fieldset->addField(
                'code',
                'editor',
                [
                    'name' => 'code',
                    'label' => __('Template Rule'),
                    'title' =>  __('Template Rule'),
                    'rows' => '20',
                    'cols' => '30',
                    'wysiwyg' => true,
                    'config' => $this->wysiwygConfig->getConfig(),
                    'required' => true
                ]
            );
        } elseif ($fieldType === 'text') {
            $fieldset->addField(
                'code',
                'text',
                [
                    'name'      => 'code',
                    'label'     => __('Template Rule'),
                    'title'     => __('Template Rule'),
                    'required'  => true,
                ]
            );
        }
    }
}
