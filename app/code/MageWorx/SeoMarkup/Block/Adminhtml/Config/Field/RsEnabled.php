<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class RsEnabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $linkText = __('special page');
        $link = 'https://search.google.com/structured-data/testing-tool';

        $comment = __(
            "Adds the snippets that will be shown on the search engine results page. The store owner can preview the snippet data on the %link.",
            ['link' => '<a target="_blank" href="' . $link . '">' . $linkText . '</a>']
        );

        $comment .= ' ' . __(
                'By default and without any configurations, the product entity will include the following attributes: product name, preview image, rating, price, availability and website language.'
            );

        $element->setComment($comment);

        return parent::render($element);
    }
}
