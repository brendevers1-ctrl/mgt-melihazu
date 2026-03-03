<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class Markup extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $trNote = __('Note');
        $linkText   = __('rich snippets');

        $comment = __(
            "Adds %link to your existing website HTML. %br%htmlNote adding snippets to your site in the search results may take some time as the search bots do not include this information immediately.",
            [
                'link'     => '<a target="_blank" href="https://support.mageworx.com/manuals/richsnippets/#glossary">' . $linkText . '</a>',
                'br'       => '<br />',
                'htmlNote' => '<b>' . $trNote . '</b>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
