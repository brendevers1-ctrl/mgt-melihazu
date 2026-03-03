<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\SeoAI\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for product request response log search results.
 */
interface ProductRequestResponseLogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list of logs.
     *
     * @return ProductRequestResponseLogInterface[]
     */
    public function getItems();

    /**
     * Set list of logs.
     *
     * @param ProductRequestResponseLogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
