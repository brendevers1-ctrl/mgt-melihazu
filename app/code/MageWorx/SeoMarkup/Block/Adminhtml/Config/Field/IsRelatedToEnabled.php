<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class IsRelatedToEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $linkText = __('isRelatedTo snippet');
        $comment  = __(
            "A pointer to another, somehow related product (or multiple products). More %schema %link.",
            [
                'schema' => 'Schema.org',
                'link'   => '<a target="_blank" href="https://schema.org/isRelatedTo">' . $linkText . '</a>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
