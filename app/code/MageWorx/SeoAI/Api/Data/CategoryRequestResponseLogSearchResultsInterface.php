<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\SeoAI\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for category request response log search results.
 */
interface CategoryRequestResponseLogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list of logs.
     *
     * @return CategoryRequestResponseLogInterface[]
     */
    public function getItems();

    /**
     * Set list of logs.
     *
     * @param CategoryRequestResponseLogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
