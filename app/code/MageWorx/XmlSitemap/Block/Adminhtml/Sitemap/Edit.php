<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Block\Adminhtml\Sitemap;

use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use MageWorx\XmlSitemap\Model\Sitemap;

class Edit extends FormContainer
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize template edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'sitemap_id';
        $this->_blockGroup = 'MageWorx_XmlSitemap';
        $this->_controller = 'adminhtml_sitemap';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->remove('reset');
        $this->buttonList->add(
            'save_and_generate',
            [
                'label'          => __('Save & Generate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'     => 'save',
                            'target'    => '#edit_form',
                            'eventData' => [
                                'action' => ['args' => ['generate' => '1']]
                            ],
                        ],
                    ],
                ],
                'class'          => 'add'
            ]
        );
    }

    /**
     * Retrieve text for header element depending on loaded template
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Sitemap $sitemap */
        $sitemap = $this->coreRegistry->registry('mageworx_xmlsitemap_sitemap');
        if ($sitemap && $sitemap->getId()) {
            return __("Edit Sitemap '%1'", $this->escapeHtml($sitemap->getSitemapFilename()));
        }

        return __('New Sitemap');
    }
}
