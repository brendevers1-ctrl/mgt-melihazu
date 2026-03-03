<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoMarkup\Helper\DataProvider;

use MageWorx\SeoMarkup\Helper\Product as ProductHelper;
use MageWorx\SeoMarkup\Helper\DataProvider\Product as DataProviderHelper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;

/**
 * Class selects related product for main product
 */
class RelatedProducts extends AbstractHelper
{
    protected ProductHelper $helperProduct;
    protected DataProviderHelper $helperDataProvider;

    public function __construct(
        ProductHelper $helperProduct,
        DataProviderHelper $helperDataProvider,
        Context $context
    )
    {
        $this->helperProduct = $helperProduct;
        $this->helperDataProvider = $helperDataProvider;
        parent::__construct($context);
    }

    /**
     * Prepare data for IsRelatedTo property
     * @link https://schema.org/isRelatedTo
     * @param Product $product
     * @return array
     */
    public function getIsRelatedToData(\Magento\Catalog\Model\Product $product): array
    {
        if(!$this->helperProduct->isRelatedToEnabled()) {
            return [];
        }

        $relatedProductList = [];

        $collection = $this->getRelatedProductCollection($product);

        $index = 0;
        foreach ($collection as $relatedProduct) {
            $relatedProductList[$index] = [
                '@context' => 'http://schema.org',
                '@type'    => 'Product',
                'name' => $relatedProduct->getName(),
                'description' => $this->helperDataProvider->getDescriptionValue($relatedProduct),
                'url' => $relatedProduct->getProductUrl()
            ];
            $image = $this->helperDataProvider->getProductImage($relatedProduct, 'product_page_image_large')
                                              ->getImageUrl();
            if($image) {
                $relatedProductList[$index]['image'] = $image;
            }

            $index++;
        }

        return $relatedProductList;
    }

    protected function  getRelatedProductCollection(\Magento\Catalog\Model\Product $product): Collection
    {
        return $product->getRelatedProductCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('meta_title')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('short_description')
                ->addAttributeToSelect('meta_description')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail');
    }
}