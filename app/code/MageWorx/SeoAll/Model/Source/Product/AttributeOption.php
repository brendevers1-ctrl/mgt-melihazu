<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model\Source\Product;

use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as AttributeOptionCollectionFactory;

class AttributeOption
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array('attribute_id' => array('value' => '<value>', 'label' => '<label>'), ...), ...)
     */
    protected $options = [];

    /**
     * @var AttributeOptionCollectionFactory
     */
    protected $attributeOptionCollectionFactory;

    /**
     * @var AttributeRepository
     */
    protected $productAttributeRepository;

    /**
     * AttributeOption constructor.
     *
     * @param AttributeRepository $productAttributeRepository
     * @param AttributeOptionCollectionFactory $attributeOptionCollectionFactory
     */
    public function __construct(
        AttributeRepository $productAttributeRepository,
        AttributeOptionCollectionFactory $attributeOptionCollectionFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attributeOptionCollectionFactory  = $attributeOptionCollectionFactory;
    }

    /**
     * @param int $attributeId
     * @return array
     */
    public function toOptionArray($attributeId)
    {
        if (!array_key_exists($attributeId, $this->options)) {

            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection */
            $attributeOptionCollection = $this->attributeOptionCollectionFactory->create();

            $this->options[$attributeId] = $attributeOptionCollection
                ->setPositionOrder('asc')
                ->setAttributeFilter($attributeId)
                ->setStoreFilter()
                ->load()
                ->toOptionArray();
        }

        array_unshift($this->options[$attributeId], ['value' => '0', 'label' =>  __('All Values')]);

        return $this->options[$attributeId];
    }

    /**
     * Get options in "key-value" format
     *
     * @param int $attributeId
     * @return array
     */
    public function toArray($attributeId)
    {
        $tmpOptions = $this->toOptionArray($attributeId);
        $options    = [];
        foreach ($tmpOptions as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}

