<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogSearchResultsInterface as SearchResultsInterface;

interface CategoryRequestResponseLogRepositoryInterface
{
    /**
     * Save product request response log.
     *
     * @param CategoryRequestResponseLogInterface $log
     * @return CategoryRequestResponseLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CategoryRequestResponseLogInterface $log): CategoryRequestResponseLogInterface;

    /**
     * Retrieve log.
     *
     * @param int $logId
     * @return CategoryRequestResponseLogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $logId): CategoryRequestResponseLogInterface;

    /**
     * Delete log.
     *
     * @param CategoryRequestResponseLogInterface $log
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CategoryRequestResponseLogInterface $log): bool;

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
