<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class ShippingDetailsEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        $linkText = __('Shipping Details markup');
        $comment  = __(
            "This setting allows you to enable the %link for the products.",
            [
                'link' => '<a target="_blank" href="https://developers.google.com/search/docs/appearance/structured-data/product#product-with-shipping-example">' . $linkText . '</a>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
