<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source;

use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as AttributeCollection;

/**
 * Used in creating options for config value selection
 *
 */
class CategoryAttributes extends \MageWorx\SeoMarkup\Model\Source
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @param AttributeCollection $attributeCollection
     */
    public function __construct(AttributeCollection $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $options = [];
            foreach ($this->attributeCollection as $attribute) {
                $attributeCode           = $attribute->getData('attribute_code');
                $frontendLabel           = $attribute->getData('frontend_label');
                $frontendLabel           = $frontendLabel ? ' (' . $frontendLabel . ')' : '';
                $options[$attributeCode] = $attributeCode . $frontendLabel;
            }
            array_unshift($options, __('-- Please Select --'));
            $this->options = $options;
        }

        return $this->options;
    }
}
