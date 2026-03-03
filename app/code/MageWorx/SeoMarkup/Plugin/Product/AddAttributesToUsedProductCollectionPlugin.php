<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Plugin\Product;

use MageWorx\SeoMarkup\Helper\Product as HelperProduct;


/**
 * Class AddAttributesToUsedProductCollectionPlugin
 *
 * Here is the temporary solution for adding attributes to related products collection.
 * @see https://github.com/magento/magento2/issues/24483
 *
 * We can leave this code for avoid multiple loading with different cache keys.
 */
class AddAttributesToUsedProductCollectionPlugin
{
    /**
     * AddAttributesToUsedProductCollectionPlugin constructor.
     *
     * @param HelperProduct $helperProduct
     */
    public function __construct(
        HelperProduct $helperProduct
    ) {
        $this->helperProduct = $helperProduct;
    }

    /**
     * @var HelperProduct
     */
    protected $helperProduct;

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $result
     * @return mixed
     */
    public function afterGetUsedProductCollection($subject, $result)
    {
        if ($this->helperProduct->isRsEnabled()) {
            $result->addAttributeToSelect(['special_price', 'special_to_date', 'special_from_date']);
        }

        return $result;
    }
}
