<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Hreflangs;

use MageWorx\SeoBase\Helper\Hreflangs as HelperHreflangs;
use MageWorx\SeoBase\Helper\Url as HelperUrl;
use MageWorx\SeoBase\Helper\StoreUrl as HelperStore;
use Magento\Framework\UrlInterface;
use MageWorx\SeoBase\Model\HreflangsConfigReader;

class HomePage extends \MageWorx\SeoBase\Model\Hreflangs
{
    /**
     * @var \MageWorx\SeoBase\Helper\StoreUrl
     */
    protected $helperStore;

    /**
     *
     * @var \MageWorx\SeoBase\Helper\Hreflangs
     */
    protected $helperHreflangs;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\HreflangsFactory
     */
    protected $hreflangFactory;

    /**
     *
     * @var \Magento\Framework\View\Layout;
     */
    protected $layout;

    /**
     * HomePage constructor.
     *
     * @param HreflangsConfigReader $hreflangsConfigReader
     * @param HelperUrl $helperUrl
     * @param HelperStore $helperStore
     * @param HelperHreflangs $helperHreflangs
     * @param UrlInterface $url
     * @param \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\HreflangsFactory $hreflangFactory
     * @param \Magento\Framework\View\Layout $layout
     * @param $fullActionName
     */
    public function __construct(
        HreflangsConfigReader $hreflangsConfigReader,
        HelperUrl $helperUrl,
        HelperStore $helperStore,
        HelperHreflangs $helperHreflangs,
        UrlInterface $url,
        \MageWorx\SeoBase\Model\ResourceModel\Cms\Page\HreflangsFactory $hreflangFactory,
        \Magento\Framework\View\Layout $layout,
        string $fullActionName
    ) {
        $this->helperStore     = $helperStore;
        $this->url             = $url;
        $this->helperHreflangs = $helperHreflangs;
        $this->hreflangFactory = $hreflangFactory;
        $this->layout          = $layout;
        parent::__construct($hreflangsConfigReader, $helperUrl, $fullActionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getHreflangUrls()
    {
        if ($this->isCancelHreflangs()) {
            return null;
        }

        $page       = $this->getPage();
        $pageId     = (empty($page) || !is_object($page)) ? 0 : $page->getId();
        $currentUrl = $this->url->getCurrentUrl();

        if (strpos($currentUrl, '?') === false || $this->isGraphQl($this->url)) {
            $hreflangCodes = $this->helperHreflangs->getHreflangFinalCodes('cms');

            if (empty($hreflangCodes)) {
                return null;
            }

            $hreflangResource = $this->hreflangFactory->create();
            $hreflangUrlsData = $hreflangResource->getHreflangsDataForHomePage(
                array_keys($hreflangCodes),
                $pageId,
                $this->isGraphQl($this->url)
            );

            if (empty($hreflangUrlsData[$pageId]['hreflangUrls'])) {
                return null;
            }

            $hreflangUrls = [];
            foreach ($hreflangUrlsData[$pageId]['hreflangUrls'] as $store => $altUrl) {
                $hreflang                = $hreflangCodes[$store];
                $hreflangUrls[$hreflang] = $altUrl;
            }
        }

        return (!empty($hreflangUrls)) ? $hreflangUrls : null;
    }

    /**
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
            return $block->getPage();
        }

        return null;
    }
}
