<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\ProductFormModifier;

class CustomCanonical extends AbstractModifier
{

    /**
     * @var ProductFormModifier
     */
    protected $productFormModifier;

    /**
     * AddToProductFormPlugin constructor.
     *
     * @param ProductFormModifier $productFormModifier
     */
    public function __construct(
        ProductFormModifier $productFormModifier
    ) {
        $this->productFormModifier = $productFormModifier;
    }


    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $this->productFormModifier->modifyMeta($meta);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}