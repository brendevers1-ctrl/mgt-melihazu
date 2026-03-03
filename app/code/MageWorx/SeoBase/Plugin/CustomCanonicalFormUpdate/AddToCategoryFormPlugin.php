<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Plugin\CustomCanonicalFormUpdate;

use MageWorx\SeoBase\Ui\DataProvider\Form\Modifier\CustomCanonical\CategoryFormModifier;

class AddToCategoryFormPlugin
{
    /**
     * @var CategoryFormModifier
     */
    protected $categoryFormModifier;

    /**
     * AddToCategoryFormPlugin constructor.
     *
     * @param CategoryFormModifier $categoryFormModifier
     */
    public function __construct(
        CategoryFormModifier $categoryFormModifier
    ) {
        $this->categoryFormModifier = $categoryFormModifier;
    }

    /**
     * @param \Magento\Ui\DataProvider\AbstractDataProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetMeta(\Magento\Ui\DataProvider\AbstractDataProvider $subject, $result)
    {
        return $this->categoryFormModifier->modifyMeta($result);
    }
}