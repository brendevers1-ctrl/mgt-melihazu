<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model;

class ExcludeCategoriesRegistry
{
    /**
     * @var array
     */
    protected $excludeCategoriesIds = [];

    /**
     * @param $templateId
     * @param array $categoriesIds
     * @return array
     */
    public function addCategoriesIds($templateId, array $categoriesIds)
    {
        if (empty($this->excludeCategoriesIds[$templateId])) {
            $this->excludeCategoriesIds[$templateId] = [];
        }

        $this->excludeCategoriesIds[$templateId] = array_merge($this->excludeCategoriesIds[$templateId], $categoriesIds);
        return $this->excludeCategoriesIds;
    }

    /**
     * @param int $templateId
     * @return array
     */
    public function getCategoriesIds($templateId)
    {
        if (!empty( $this->excludeCategoriesIds[$templateId])) {
            return $this->excludeCategoriesIds[$templateId];
        }

        return [];
    }
}