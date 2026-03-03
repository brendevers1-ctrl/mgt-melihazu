<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class FreeShippingEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $linkText = __('Free shipping markup');
        $comment  = __(
            "This setting allows you to enable the %link for the products.",
            [
                'link' => '<a target="_blank" href="https://developers.google.com/search/blog/2020/09/new-schemaorg-support-for-retailer">' . $linkText . '</a>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
