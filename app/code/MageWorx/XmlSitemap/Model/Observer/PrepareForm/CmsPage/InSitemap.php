<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Observer\PrepareForm\CmsPage;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class InSitemap implements ObserverInterface
{

    /**
     * Add values for "in_xml_sitemap" field
     * Event: adminhtml_cms_page_edit_tab_meta_prepare_form
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $form     = $observer->getForm();
        $fieldset = $form->getElements()->searchById('meta_fieldset');

        $fieldset->addField(
            'in_xml_sitemap',
            'select',
            [
                'name'   => 'in_xml_sitemap',
                'label'  => __('In XML Sitemap'),
                'title'  => __('In XML Sitemap'),
                'values' => [0 => __('No'), 1 => __('Yes')],
                'note'   => __('This setting was added by MageWorx XML Sitemap')
            ]
        );

        return $this;
    }
}
