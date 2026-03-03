<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Plugin\CustomCanonicalFormUpdate;

use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\ProductFormModifier;

class AddToProductFormPlugin
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
     * @param \Magento\Ui\DataProvider\AbstractDataProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetMeta(\Magento\Ui\DataProvider\AbstractDataProvider $subject, $result)
    {
        return $this->productFormModifier->modifyMeta($subject, $result);
    }
}