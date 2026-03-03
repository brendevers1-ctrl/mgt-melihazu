<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model;

class ExcludeProductsRegistry
{
    /**
     * @var array
     */
    protected $excludeProductIds = [];

    /**
     * @param int $templateId
     * @param array $productIds
     * @return array
     */
    public function addProduct($templateId, array $productIds)
    {
        if (empty($this->excludeProductIds[$templateId])) {
            $this->excludeProductIds[$templateId] = [];
        }

        $this->excludeProductIds[$templateId] = array_merge($this->excludeProductIds[$templateId], $productIds);
        return $this->excludeProductIds;
    }

    /**
     * @param int $templateId
     * @return array
     */
    public function getProducts($templateId)
    {
        if (!empty( $this->excludeProductIds[$templateId])) {
            return $this->excludeProductIds[$templateId];
        }

        return [];
    }
}