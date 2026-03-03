<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer\PrepareForm\Catalog;

class CrossDomainStore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageWorx\SeoBase\Model\Source\CrossDomainStore
     */
    protected $options;

    /**
     * @param \MageWorx\SeoBase\Model\Source\CrossDomainStore $crossDomainStoreOptions
     */
    public function __construct(
        \MageWorx\SeoBase\Model\Source\CrossDomainStore $crossDomainStoreOptions
    ) {
        $this->options = $crossDomainStoreOptions;
    }

    /**
     * Add values for "cross_domain_store" field
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //adminhtml_catalog_product_edit_prepare_form
        $form = $observer->getForm();
        $crossDomainStoreField = $form->getElement('cross_domain_store');

        if ($crossDomainStoreField) {
            $crossDomainStoreField->setValues($this->options->toOptionArray());
        }

        return $this;
    }
}
