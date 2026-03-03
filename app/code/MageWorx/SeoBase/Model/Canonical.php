<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model;

use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;

abstract class Canonical implements \MageWorx\SeoBase\Model\CanonicalInterface
{
    /**
     * @var \MageWorx\SeoBase\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SeoBase\Helper\Url
     */
    protected $helperUrl;

    /**
     * @var \MageWorx\SeoBase\Helper\StoreUrl
     */
    protected $helperStoreUrl;

    /**
     * @var CustomCanonicalRepositoryInterface
     */
    protected $customCanonicalRepository;

    /**
     * @var string
     */
    protected $fullActionName;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $entity;

    /**
     * @return string
     */
    abstract public function getCanonicalUrl();

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        CustomCanonicalRepositoryInterface $customCanonicalRepository,
        $fullActionName = ''
    ) {
        $this->helperData                = $helperData;
        $this->helperUrl                 = $helperUrl;
        $this->helperStoreUrl            = $helperStoreUrl;
        $this->customCanonicalRepository = $customCanonicalRepository;
        $this->fullActionName            = $fullActionName;
    }

    /**
     * Crop or add trailing slash
     *
     * @param string $url
     * @param int|null $storeId
     * @param bool $isHomePage
     * @return string
     */
    public function trailingSlash(string $url, int $storeId = null, bool $isHomePage = false): string
    {
        return $this->helperStoreUrl->trailingSlash($url, $storeId, $isHomePage);
    }

    /**
     * @param string $fullActionName
     * @return Canonical
     */
    public function setFullActionName(string $fullActionName): Canonical
    {
        $this->fullActionName = $fullActionName;

        return $this;
    }

    /**
     * Check if cancel adding canonical URL by config settings
     *
     * @return bool
     */
    protected function isCancelCanonical(): bool
    {
        if ($this->helperData->isCanonicalUrlEnabled()) {
            if ($this->fullActionName == 'mageworx_landingpagespro_landingpage_view') {
                return true;
            }

            return in_array($this->fullActionName, $this->helperData->getCanonicalIgnorePages());
        }

        return true;
    }

    /**
     * Prepare ULR to output
     *
     * @param string $url
     * @return string
     */
    public function renderUrl(string $url): string
    {
        return $this->helperUrl->escapeUrl($this->trailingSlash($url));
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity): Canonical
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param  \Magento\Framework\UrlInterface $url
     * @return bool
     */
    protected function isGraphQl($url)
    {
        return in_array(parse_url($url->getCurrentUrl(), PHP_URL_PATH), ['/graphql', '/graphql/']);
    }

    /**
     * @param int $entityId
     * @return null
     */
    public function getCanonicalStoreId($entityId)
    {
        return null;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getUrlPathFromUrl($url, $storeId = null)
    {
        if ($storeId) {
            $baseUrl = $this->helperStoreUrl->getStoreBaseUrl($storeId);
        } else {
            $parts = explode('?', $this->url->getBaseUrl());
            $baseUrl = $parts[0];
        }

        return str_replace($baseUrl, '', $url);
    }
}
