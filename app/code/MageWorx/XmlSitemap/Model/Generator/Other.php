<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\XmlSitemap\Model\Generator;

use MageWorx\SeoAll\Helper\MagentoVersion;
use MageWorx\XmlSitemap\Model\WriterInterface;
use MageWorx\XmlSitemap\Model\OtherSitemapItemsAdapter;

class Other extends AbstractGenerator
{
    /**
     * @var OtherSitemapItemsAdapter
     */
    protected $otherSitemapItemsAdapter;

    /**
     * @var MagentoVersion
     */
    protected $helperVersion;

    /**
     * Other constructor.
     *
     * @param \MageWorx\XmlSitemap\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param OtherSitemapItemsAdapter $otherSitemapItemsAdapter
     * @param MagentoVersion $helperVersion
     */
    public function __construct(
        \MageWorx\XmlSitemap\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        OtherSitemapItemsAdapter $otherSitemapItemsAdapter,
        MagentoVersion $helperVersion
    ) {
        parent::__construct($helper, $objectManager);
        $this->code = 'other';
        $this->name = __('Other');

        $this->otherSitemapItemsAdapter = $otherSitemapItemsAdapter;
        $this->helperVersion            = $helperVersion;
    }

    /**
     * @param int $storeId
     * @param WriterInterface $writer
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate($storeId, $writer)
    {
        $this->storeBaseUrl = $writer->storeBaseUrl;
        $sitemapItems       = $this->otherSitemapItemsAdapter->getItems((int)$storeId);

        foreach ($sitemapItems as $sitemapItem) {
            //Since Magento 2.3
            if ($this->helperVersion->checkModuleVersion('Magento_Sitemap', '100.3.0')) {
                $writer->write(
                    $this->getUrl((string)$sitemapItem->getUrl()),
                    $this->getLastMod((string)$sitemapItem->getUpdatedAt()),
                    $sitemapItem->getChangeFrequency(),
                    $sitemapItem->getPriority(),
                    $sitemapItem->getImages()
                );
                $this->counter++;
            } else {
                $changefreq = $sitemapItem->getChangefreq();
                $priority   = $sitemapItem->getPriority();

                foreach ($sitemapItem->getCollection() as $item) {
                    $writer->write(
                        $this->getUrl((string)$item->getUrl()),
                        $this->getLastMod((string)$item->getUpdatedAt()),
                        $changefreq,
                        $priority,
                        $item->getImages()
                    );
                    $this->counter++;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getUrl(string $url): string
    {
        if (strpos($url, '://') !== false) {
            return $url;
        }

        return $this->storeBaseUrl . ltrim($url, '/');
    }

    /**
     * @param string $updatedAt
     * @return string
     */
    protected function getLastMod(string $updatedAt): string
    {
        if ($updatedAt) {
            $timestamp = strtotime($updatedAt);
            return date('c', $timestamp);
        } else {
            return $this->helper->getCurrentDate();
        }
    }
}
