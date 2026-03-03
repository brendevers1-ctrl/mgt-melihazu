<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class SellerType extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $label = __(
            'Type (by %schema)',
            [
                'schema' => 'Schema.org'
            ]
        );

        $linkText = __('type');
        $comment = __(
            "Select your business %link",
            [
                'link' => '<a target="_blank" href="https://schema.org/Store">' . $linkText . '</a>'
            ]
        );

        $element->setLabel($label);
        $element->setComment($comment);

        return parent::render($element);
    }
}
