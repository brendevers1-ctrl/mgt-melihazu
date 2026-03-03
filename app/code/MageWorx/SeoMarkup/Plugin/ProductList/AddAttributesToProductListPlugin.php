<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Plugin\ProductList;

class AddAttributesToProductListPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Category
     */
    protected $helperCategory;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * AddAttributesToProductListPlugin constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \MageWorx\SeoMarkup\Helper\Category $helperCategory
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \MageWorx\SeoMarkup\Helper\Category $helperCategory,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct
    ) {
        $this->request        = $request;
        $this->helperCategory = $helperCategory;
        $this->helperProduct  = $helperProduct;
    }

    /**
     * @param \Magento\Catalog\Model\Layer $subject
     * @param \Magento\Catalog\Model\Layer $result
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\Layer
     */
    public function afterPrepareProductCollection($subject, $result, $collection)
    {
        if ($this->request->getFullActionName() !== 'catalog_category_view') {
            return $result;
        }

        if (!$this->helperCategory->isRsEnabled()) {
            return $result;
        }

        $this->addBrandAttributeToSelect($collection);
        $this->addColorAttributeToSelect($collection);
        $this->addGtinAttributeToSelect($collection);
        $this->addManufacturerAttributeToSelect($collection);
        $this->addSkuAttributeToSelect($collection);
        $this->addConditionAttributeToSelect($collection);
        $this->addModelAttributeToSelect($collection);
        $this->addDescriptionAttributeToSelect($collection);
        $this->addFreeShippingAttributeToSelect($collection);
        $this->addMerchantReturnPolicyAttributeToSelect($collection);
        $this->addCustomPropertiesAttributesToSelect($collection);

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addBrandAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isBrandEnabled()) {
            $brandCode = $this->helperProduct->getBrandCode();

            if ($brandCode) {
                $collection->addAttributeToSelect($brandCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addColorAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isColorEnabled()) {
            $colorCode = $this->helperProduct->getColorCode();

            if ($colorCode) {
                $collection->addAttributeToSelect($colorCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addGtinAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isGtinEnabled()) {
            $gtinCode = $this->helperProduct->getGtinCode();

            if ($gtinCode) {
                $collection->addAttributeToSelect($gtinCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addManufacturerAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isManufacturerEnabled()) {
            $manufacturerCode = $this->helperProduct->getManufacturerCode();

            if ($manufacturerCode) {
                $collection->addAttributeToSelect($manufacturerCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addSkuAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isSkuEnabled()) {
            $skuCode = $this->helperProduct->getSkuCode();

            if ($skuCode) {
                $collection->addAttributeToSelect($skuCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addConditionAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isConditionEnabled()) {
            $conditionCode = $this->helperProduct->getConditionCode();

            if ($conditionCode) {
                $collection->addAttributeToSelect($conditionCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addModelAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isModelEnabled()) {
            $modelCode = $this->helperProduct->getModelCode();

            if ($modelCode) {
                $collection->addAttributeToSelect($modelCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addDescriptionAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        $descriptionCode = $this->helperProduct->getDescriptionCode();

        if ($descriptionCode) {
            $collection->addAttributeToSelect($descriptionCode);
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addFreeShippingAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isShippingDetailsEnabled() && $this->helperProduct->isFreeShippingEnabled()) {
            $freeShippingCode = $this->helperProduct->getFreeShippingCode();

            if ($freeShippingCode) {
                $collection->addAttributeToSelect($freeShippingCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addMerchantReturnPolicyAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        if ($this->helperProduct->isMerchantReturnPolicyEnabled()) {
            $merchantReturnPolicyCode = $this->helperProduct->getMerchantReturnPolicyCode();

            if ($merchantReturnPolicyCode) {
                $collection->addAttributeToSelect($merchantReturnPolicyCode);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    protected function addCustomPropertiesAttributesToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): void {
        $customProperties = $this->helperProduct->getCustomProperties();

        foreach ($customProperties as $propertyCode) {
            $collection->addAttributeToSelect($propertyCode);
        }
    }
}
