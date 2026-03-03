<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Hreflangs;

use MageWorx\SeoBase\Helper\Url as HelperUrl;
use MageWorx\SeoBase\Model\HreflangsConfigReader;
use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\HreflangsFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class Product extends \MageWorx\SeoBase\Model\Hreflangs
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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\HreflangFactory
     */
    protected $hreflangFactory;

    /**
     * Product constructor.
     *
     * @param HreflangsConfigReader $hreflangsConfigReader
     * @param HelperUrl $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStore
     * @param \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs
     * @param Registry $registry
     * @param UrlInterface $url
     * @param HreflangsFactory $hreflangFactory
     * @param string $fullActionName
     */
    public function __construct(
        HreflangsConfigReader $hreflangsConfigReader,
        HelperUrl $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStore,
        \MageWorx\SeoBase\Helper\Hreflangs $helperHreflangs,
        Registry $registry,
        UrlInterface $url,
        HreflangsFactory $hreflangFactory,
        $fullActionName
    ) {
        $this->registry        = $registry;
        $this->helperStore     = $helperStore;
        $this->url             = $url;
        $this->helperHreflangs = $helperHreflangs;
        $this->hreflangFactory = $hreflangFactory;
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

        $product = $this->getEntity();

        if (!$product) {
            $product = $this->registry->registry('current_product');
            if (empty($product) || !is_object($product)) {
                return null;
            }
        }

        $productId  = $product->getId();
        $currentUrl = $this->url->getCurrentUrl();

        if (strpos($currentUrl, '?') === false || $this->isGraphQl($this->url)) {
            $hreflangCodes = $this->helperHreflangs->getHreflangFinalCodes('product');
            if (empty($hreflangCodes)) {
                return null;
            }

            $hreflangResource = $this->hreflangFactory->create();
            $hreflangUrlsData = $hreflangResource->getHreflangsData(
                array_keys($hreflangCodes),
                [$productId],
                $this->isGraphQl($this->url)
            );

            if (empty($hreflangUrlsData[$productId]['hreflangUrls'])) {
                return null;
            }

            $hreflangUrls = [];
            foreach ($hreflangUrlsData[$productId]['hreflangUrls'] as $store => $altUrl) {
                if ($hreflangUrlsData[$productId]['requestPath'] != null) {
                    $hreflang                = $hreflangCodes[$store];
                    $hreflangUrls[$hreflang] = $altUrl;
                }
            }
        }

        return empty($hreflangUrls) ? null : $hreflangUrls;
    }
}
