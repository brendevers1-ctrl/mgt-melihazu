<?php

declare(strict_types=1);

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

class ProductAttributes implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $attributeCollectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Get all product attributes
     *
     * @return array
     */
    public function getAllProductAttributes(): array
    {
        // get attribute collection
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addVisibleFilter();

        //@TODO: Temporary remove pre-generated dummy attributes
        $attributeCollection->getSelect()
            ->where('main_table.attribute_code NOT LIKE "attribute_%"');

        // get all attributes
        $attributes = [];
        foreach ($attributeCollection as $attribute) {
            $attributes[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $attributes;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return $this->getAllProductAttributes();
    }
}
