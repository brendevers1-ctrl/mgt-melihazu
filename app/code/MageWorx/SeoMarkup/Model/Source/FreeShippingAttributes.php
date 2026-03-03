<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;

class FreeShippingAttributes extends \MageWorx\SeoMarkup\Model\Source
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * FreeShippingAttributes constructor.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(AttributeCollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $collection = $this->attributeCollectionFactory->create()->addFieldToFilter(
                AttributeInterface::SOURCE_MODEL,
                ['eq' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean']
            );
            $options = [];
            foreach ($collection as $attribute) {
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
