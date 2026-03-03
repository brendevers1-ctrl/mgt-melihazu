<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class CategoryEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $linkText = __('category snippet');
        $comment = __(
            "Enable to match the product category to %schema %link.",
            [
                'schema' => 'Schema.org',
                'link'   => '<a target="_blank" href="https://pending.schema.org/category">' . $linkText . '</a>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
