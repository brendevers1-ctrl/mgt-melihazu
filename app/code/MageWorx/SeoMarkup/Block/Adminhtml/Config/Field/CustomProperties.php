<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Adminhtml\Config\Field;

class CustomProperties extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $trNote  = __('Example');
        $comment = __(
            'A comma-delimited list of %schema_link property name and attribute codes.%br%htmlNote%br mpn,mpn_code %br For JSON-LD will be converted to: %br { ... "mpn":"12343" ... } %br',
            [
                'schema_link' => '<a href="http://schema.org/Product">schema.org</a>',
                'br'          => '<br />',
                'htmlNote'    => '<b>' . $trNote . '</b>'
            ]
        );

        $element->setComment($comment);

        return parent::render($element);
    }
}
