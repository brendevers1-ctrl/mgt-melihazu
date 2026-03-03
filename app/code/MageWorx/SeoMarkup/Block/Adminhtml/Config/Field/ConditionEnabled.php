<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class ConditionEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $link     = 'https://schema.org/weight';
        $linkText = __('priority of the categories');

        $comment = __(
            "Map any existing product attribute to the Schema.org %link.",
            ['link' => '<a target="_blank" href="' . $link . '">' . $linkText . '</a>']
        );

        $linkToNew             = 'https://schema.org/NewCondition';
        $linkToUsed            = 'https://schema.org/UsedCondition';
        $linkToRefurbished     = 'https://schema.org/RefurbishedCondition';
        $linkToDamaged         = 'https://schema.org/DamagedCondition';
        $linkTextToNew         = 'New';
        $linkTextToUsed        = 'Used';
        $linkTextToRefurbished = 'Refurbished';
        $linkTextToDamaged     = 'Damaged';

        $comment .= ' ' . __(
                "Assign the current product attribute options to the Schema.org condition options such as %new, %used, %refurbished or %damaged.",
                [
                    'new'         => '<a target="_blank" href="' . $linkToNew . '">' . $linkTextToNew . '</a>',
                    'used'        => '<a target="_blank" href="' . $linkToUsed . '">' . $linkTextToUsed . '</a>',
                    'refurbished' => '<a target="_blank" href="' . $linkToRefurbished . '">' . $linkTextToRefurbished . '</a>',
                    'damaged'     => '<a target="_blank" href="' . $linkToDamaged . '">' . $linkTextToDamaged . '</a>'
                ]
            );

        $element->setComment($comment);

        return parent::render($element);
    }
}
