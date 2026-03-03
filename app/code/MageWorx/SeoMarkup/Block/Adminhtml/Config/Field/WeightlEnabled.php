<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class WeightlEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $linkText = __('weight snippet');
        $comment  = __(
            "Enable to match the product weight to %schema %link.",
            [
                'schema' => 'Schema.org',
                'link'   => '<a target="_blank" href="https://schema.org/weight">' . $linkText . '</a>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
