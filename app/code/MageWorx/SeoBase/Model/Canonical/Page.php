<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Canonical;

use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Page extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string[]|null
     */
    protected $canonicalUrls;

    /**
     * @var array
     */
    protected $canonicalStoreIds = [];

    /**
     * Page constructor.
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        CustomCanonicalRepositoryInterface $customCanonicalRepository,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\Layout $layout,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $fullActionName = 'cms_page_view'
    ) {
        $this->layout       = $layout;
        $this->url          = $url;
        $this->storeManager = $storeManager;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $customCanonicalRepository, $fullActionName);
    }

    /**
     * Retrieve CMS pages canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }

        $page = $this->getPage();

        if (!$page) {
            return null;
        }

        $pageId = (int)$page->getId();

        if (isset($this->canonicalUrls[$pageId])) {
            return $this->canonicalUrls[$pageId];
        }

        $canonicalUrl = $this->getCustomCanonicalUrl($page);

        if (!$canonicalUrl && $this->isHomePage($page)) {
            $canonicalUrl = $this->getCanonicalUrlForHomePage();
        }

        if ($canonicalUrl === null) {

            if ($this->isGraphQl($this->url)) {
                $pageUrl = $page->getData('identifier');
            } else {
                $pageUrl = $this->helperStoreUrl->getUrl($page->getData('identifier'));
            }

            $canonicalUrl = $this->renderUrl($this->helperUrl->deleteAllParametrsFromUrl($pageUrl));
        }

        $this->canonicalUrls[$pageId] = $canonicalUrl;

        return $this->canonicalUrls[$pageId];
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCustomCanonicalUrl(\Magento\Cms\Model\Page $page)
    {
        $customCanonical = $this->customCanonicalRepository->getBySourceEntityData(
            Rewrite::ENTITY_TYPE_CMS_PAGE,
            $page->getId(),
            $this->storeManager->getStore()->getId(),
            false
        );

        if ($customCanonical) {

            $canonicalStoreId = null;
            $canonicalUrl = $this->customCanonicalRepository->getCustomCanonicalUrl(
                $customCanonical,
                $this->storeManager->getStore()->getId(),
                $canonicalStoreId,
                $this->isGraphQl($this->url)
            );

            if ($canonicalUrl) {
                $this->canonicalStoreIds[$page->getId()] = $canonicalStoreId;

                return $this->renderUrl($canonicalUrl);
            }
        }

        return null;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCanonicalUrlForHomePage(): string
    {
        if ($this->isGraphQl($this->url)) {
            return '';
        }
        $storeId = (int)$this->storeManager->getStore()->getStoreId();
        $urlRaw  = $this->getStoreBaseUrl($storeId);
        $url     = $this->trailingSlash($urlRaw, $storeId, true);

        return $this->helperUrl->escapeUrl($url);
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @return bool
     */
    protected function isHomePage(\Magento\Cms\Model\Page $page)
    {
        $homePageId     = null;
        $homeIdentifier = $this->helperData->getHomeIdentifier();

        if (strpos($homeIdentifier, '|') !== false) {
            list($homeIdentifier, $homePageId) = explode('|', $homeIdentifier);
        }

        return $homeIdentifier == $page->getIdentifier();
    }

    /**
     * Get store base url
     *
     * @param int $storeId
     * @param string $type
     * @return string
     */
    public function getStoreBaseUrl(
        int $storeId = null,
        string $type = \Magento\Framework\UrlInterface::URL_TYPE_LINK
    ): string {
        return rtrim($this->storeManager->getStore($storeId)->getBaseUrl($type), '/') . '/';
    }

    /**
     * Retrieve current CMS page model from layout
     *
     * @return \Magento\Cms\Model\Page|null
     */
    protected function getPage()
    {
        $page = $this->getEntity();

        if ($page instanceof \Magento\Cms\Model\Page) {
            return $page;
        }

        $block = $this->layout->getBlock('cms_page');
        if (is_object($block)) {
            $page = $block->getPage();
            if (is_object($page)) {
                return $page;
            }
        }

        return null;
    }

    /**
     * @param int $entityId
     * @return null
     */
    public function getCanonicalStoreId($entityId)
    {
        if (array_key_exists($entityId, $this->canonicalStoreIds)) {
            return $this->canonicalStoreIds[$entityId];
        }

        return null;
    }
}
