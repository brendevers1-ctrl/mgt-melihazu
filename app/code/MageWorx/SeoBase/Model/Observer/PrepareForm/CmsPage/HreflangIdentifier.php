<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Observer\PrepareForm\CmsPage;

use \MageWorx\SeoBase\Helper\Hreflangs as HelperHreflangs;
use MageWorx\SeoBase\Model\HreflangsConfigReader;

class HreflangIdentifier implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var HreflangsConfigReader
     */
    protected $hreflangsConfigReader;

    /**
     * HreflangIdentifier constructor.
     *
     * @param HreflangsConfigReader $hreflangsConfigReader
     */
    public function __construct(HreflangsConfigReader $hreflangsConfigReader)
    {
        $this->hreflangsConfigReader = $hreflangsConfigReader;
    }

    /**
     * Add "Hreflang Identifier" field
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //adminhtml_cms_page_edit_tab_meta_prepare_form
        $form   = $observer->getForm();
        $fieldset = $form->getElements()->searchById('meta_fieldset');

        $fieldset->addField(
            'mageworx_hreflang_identifier',
            'text',
            [
                'name'   => 'mageworx_hreflang_identifier',
                'label'  => __('Hreflang Identifier'),
                'title'  => __('Hreflang Identifier'),
                'class'  => 'validate-identifier',
                'note'   => $this->getNote(),
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    protected function getNote(): string
    {
        if ($this->hreflangsConfigReader->getCmsPageRelationWay() == HelperHreflangs::CMS_RELATION_BY_IDENTIFIER) {
            $note = __('The setting is enabled. You can see the other options in');
        } else {
            $note = __('This setting is disabled. You can enable it in');
        }
        $note .= __('<i>SEO -> SEO Hreflangs URLs</i> config section');
        $note .= '<br>' . __('This setting was added by MageWorx SEO Suite');

        return $note;
    }
}
