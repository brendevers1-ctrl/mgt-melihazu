<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Helper\DataProvider;

class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $helperOutput;

    /**
     * @var array
     */
    protected $attributeValues = [];

    /**
     * Category constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Catalog\Helper\Output $helperOutput
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Catalog\Helper\Output $helperOutput,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->resourceCategory = $resourceCategory;
        $this->helperOutput     = $helperOutput;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param string $attributeCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeValueByCode(\Magento\Catalog\Model\Category $category, string $attributeCode): string
    {
        if (!empty($this->attributeValues[$category->getId()])
            && array_key_exists($attributeCode, $this->attributeValues[$category->getId()])
        ) {
            return $this->attributeValues[$category->getId()][$attributeCode];
        }

        $attribute = $this->resourceCategory->getAttribute($attributeCode);
        $value     = trim((string)$attribute->getFrontend()->getValue($category));

        if (strlen($value)) {
            $value = $this->helperOutput->categoryAttribute($category, $value, $attribute->getAttributeCode());
        }

        $this->attributeValues[$category->getId()][$attributeCode] = $value;

        return $this->attributeValues[$category->getId()][$attributeCode];
    }
}
