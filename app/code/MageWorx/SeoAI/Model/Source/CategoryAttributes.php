<?php

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as CategoryAttributesCollection;
class CategoryAttributes implements OptionSourceInterface
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
     * Get all category attributes
     *
     * @return array
     */
    public function getAllCategoryAttributes(): array
    {
        // get attribute collection
        /** @var CategoryAttributesCollection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();

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
        return $this->getAllCategoryAttributes();
    }
}
