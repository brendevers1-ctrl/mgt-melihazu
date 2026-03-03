<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Block\Adminhtml\Sitemap\Edit;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use MageWorx\XmlSitemap\Model\Sitemap;
use MageWorx\XmlSitemap\Model\Source\EntityType;
use MageWorx\XmlSitemap\Model\Source\IsSplitSitemap as IsSplitSitemapOptions;

class Form extends GenericForm
{
    /**
     * @var Store
     */
    protected $store;

    /**
     * @var EntityType
     */
    protected $entityType;

    /**
     * @var IsSplitSitemapOptions
     */
    protected $isSplitSitemapOptions;

    /**
     * @var array
     */
    protected $formValues = [];

    /**
     * Form constructor.
     *
     * @param Store $store
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EntityType $entityType
     * @param IsSplitSitemapOptions $isSplitSitemapOptions
     * @param array $data
     */
    public function __construct(
        Store $store,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EntityType $entityType,
        IsSplitSitemapOptions $isSplitSitemapOptions,
        array $data = []
    ) {
        $this->store                 = $store;
        $this->entityType            = $entityType;
        $this->isSplitSitemapOptions = $isSplitSitemapOptions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('sitemap_form');
    }

    protected function _initFormValues()
    {
        $model = $this->getCurrentSitemapModel();

        if ($model && $model->getId()) {
            $this->_formValues = [
                'sitemap_id'       => $model->getId(),
                'sitemap_filename' => $model->getSitemapFilename(),
                'server_path'      => $model->getData('server_path'),
                'sitemap_path'     => $model->getData('sitemap_path'),
                'store_id'         => $model->getStoreId(),
                'entity_type'      => $model->getEntityType()
            ];
        } else {
            $this->_formValues = [
                'sitemap_id'       => '',
                'sitemap_filename' => '',
                'server_path'      => '',
                'sitemap_path'     => '',
                'store_id'         => $this->_storeManager->getStore(true)->getId(),
                'is_split_sitemap' => IsSplitSitemapOptions::SPLIT_SITEMAP_DISABLED,
            ];
        }

        return $this;
    }

    /**
     * Prepare the form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->_initFormValues();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                ],
            ]
        );

        $form->setHtmlIdPrefix('sitemap_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Sitemap')]);

        $fieldset->addField(
            'sitemap_id',
            'hidden',
            [
                'name'  => 'sitemap_id',
                'value' => $this->_formValues['sitemap_id']
            ]
        );


        $fieldset->addField(
            'sitemap_filename',
            'text',
            [
                'label'    => __('Filename'),
                'title'    => __('Filename'),
                'name'     => 'sitemap_filename',
                'required' => true,
                'value'    => $this->_formValues['sitemap_filename'],
                'note'     => __('Example: %1', 'sitemap.xml'),
            ]
        );


        $fieldset->addField(
            'server_path',
            'text',
            [
                'label'    => __('Server Path'),
                'title'    => __('Server Path'),
                'name'     => 'server_path',
                'required' => false,
                'value'    => $this->_formValues['server_path'],
                'note'     => __(
                    'Path to server root directory. Example: \'pub/\' or \'second_website/\' (path must be writeable). This path won\'t be added to its host links.'
                )
            ]
        );

        $fieldset->addField(
            'sitemap_path',
            'text',
            [
                'label'    => __('Path'),
                'title'    => __('Path'),
                'name'     => 'sitemap_path',
                'required' => true,
                'value'    => $this->_formValues['sitemap_path'],
                'note'     => __(
                    'Example: "sitemap/" or "/" for base path (path must be writeable). This path will be added to links.'
                )
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'name'     => 'store_id',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->store->getStoreValuesForForm(false, false)
                ]
            );
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'  => 'store_id',
                    'value' => $this->_formValues['store_id']
                ]
            );
        }

        $model = $this->getCurrentSitemapModel();

        if (!$model || !$model->getId()) {

            $isSplitSitemapField = $fieldset->addField(
                'is_split_sitemap',
                'select',
                [
                    'name'     => 'is_split_sitemap',
                    'label'    => __('Split Sitemap'),
                    'title'    => __('Split Sitemap'),
                    'required' => true,
                    'values'   => $this->isSplitSitemapOptions->toOptionArray(),
                    'note'     => __('Separate sitemaps will be created for selected entity types.') . ' '
                        . __('Sitemap file names will contain corresponding suffix.') . '<br>'
                        . __('E.g.: sitemap_products.xml')
                ]
            );

            $entityTypeField = $fieldset->addField(
                'entity_type',
                'multiselect',
                [
                    'name'     => 'entity_type',
                    'label'    => __('Entity Type'),
                    'title'    => __('Entity Type'),
                    'required' => true,
                    'values'   => $this->entityType->toOptionArray(),
                ]
            );

            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Form\Element\Dependence'
                )
                     ->addFieldMap($isSplitSitemapField->getHtmlId(), $isSplitSitemapField->getName())
                     ->addFieldMap($entityTypeField->getHtmlId(), $entityTypeField->getName())
                     ->addFieldDependence(
                         $entityTypeField->getName(),
                         $isSplitSitemapField->getName(),
                         IsSplitSitemapOptions::SPLIT_SITEMAP_ENABLED
                     )
            );
        } else {
            $fieldset->addField(
                'entity_type',
                'hidden',
                [
                    'name'  => 'entity_type[]',
                    'value' => $this->_formValues['entity_type']
                ]
            );
        }

        $form->setValues($this->_formValues);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get Sitemap model instance
     *
     * @return Sitemap
     */
    protected function getCurrentSitemapModel()
    {
        return $this->_coreRegistry->registry('mageworx_xmlsitemap_sitemap');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}

