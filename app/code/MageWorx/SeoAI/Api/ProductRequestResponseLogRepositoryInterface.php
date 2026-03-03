<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogSearchResultsInterface as SearchResultsInterface;

interface ProductRequestResponseLogRepositoryInterface
{
    /**
     * Save product request response log.
     *
     * @param ProductRequestResponseLogInterface $log
     * @return ProductRequestResponseLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(ProductRequestResponseLogInterface $log): ProductRequestResponseLogInterface;

    /**
     * Retrieve log.
     *
     * @param int $logId
     * @return ProductRequestResponseLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $logId): ProductRequestResponseLogInterface;

    /**
     * Delete log.
     *
     * @param ProductRequestResponseLogInterface $log
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(ProductRequestResponseLogInterface $log): bool;

    /**
     * Delete log by ID.
     *
     * @param int $logId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $logId): bool;

    /**
     * Retrieve logs matching the specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
