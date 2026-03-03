<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\XmlSitemap\Model\ResourceModel\Catalog\ProductFactory;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObject;
use MageWorx\XmlSitemap\Model\WriterInterface;
use Zend_Db_Statement_Exception;


/**
 * {@inheritdoc}
 */
class Product extends AbstractMediaGenerator
{
    /**
     * @var ProductFactory
     */
    protected $sitemapProductResourceFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Product constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param ProductFactory $sitemapProductResourceFactory
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        ProductFactory $sitemapProductResourceFactory,
        EventManagerInterface $eventManager
    ) {
        $this->code         = 'product';
        $this->sitemapProductResourceFactory = $sitemapProductResourceFactory;
        parent::__construct($helper, $objectManager);
        $this->name         = __('Products');
        $this->eventManager = $eventManager;
    }

    /**
     * @param int $storeId
     * @param WriterInterface $writer
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function generate($storeId, $writer)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;

        $priority   = $this->helper->getProductPriority($storeId);
        $changefreq = $this->helper->getProductChangefreq($storeId);

        /** @var \MageWorx\XmlSitemap\Model\ResourceModel\Catalog\Product $sitemapProductResource */
        $sitemapProductResource = $this->sitemapProductResourceFactory->create();
        $this->counter          = 0;

        while (!$sitemapProductResource->isCollectionReaded()) {
            $collection = $sitemapProductResource->getLimitedCollection(
                $storeId,
                self::COLLECTION_LIMIT,
                $this->usePubInMediaUrl
            );

            $altCodes = $alternateUrlsData = [];

            if ($this->helper->useHreflangs()) {

                $container = new \Magento\Framework\DataObject(
                    [
                        'collection'          => $collection,
                        'store_id'            => $storeId,
                        'alternate_urls_data' => $alternateUrlsData,
                        'alt_codes'           => $altCodes
                    ]
                );

                /** @see \MageWorx\SeoBase\Model\Observer\XmlSitemap\AddProductHreflangUrlsDataObserver */
                $this->eventManager->dispatch(
                    'mageworx_xmlsitemap_product_hreflang_urls',
                    ['container' => $container]
                );

                $alternateUrlsData = $container->getData('alternate_urls_data');
                $altCodes          = $container->getData('alt_codes');
            }

            /** @var DataObject $product */
            foreach ($collection as $product) {

                /** @var DataObject $images */
                $images = $this->getIsAllowedImages() ? $product->getImages() : false;

                if ($images) {
                    //we don't add thumbnail URL here
                    $this->imageCounter += count($images->getCollection());
                }

                /** @var DataObject $videos */
                $videos = $this->getIsAllowedVideo() ? $product->getVideos() : false;

                if ($videos) {
                    //we don't add thumbnail URL here
                    $this->videoCounter += count($videos->getCollection());
                }

                $alternateUrls = $this->getAlternateUrls($alternateUrlsData, $product, $altCodes);

                if ($alternateUrls) {
                    $this->counter += count($alternateUrls);
                }

                /**@var \MageWorx\XmlSitemap\Model\Writer $writer */

                $writer->setAlternateUrls($alternateUrls);

                $writer->write(
                    $this->getItemUrl($product),
                    $this->getItemChangeDate($product),
                    $changefreq,
                    $priority,
                    $images,
                    $videos
                );
            }
            $this->counter += count($collection);
            unset($collection);
        }
    }

    /**
     * @param array $alternateUrlsData
     * @param $item
     * @param array $altCodes
     * @return array
     */
    protected function getAlternateUrls(array $alternateUrlsData, $item, array $altCodes)
    {
        $alternateUrls = [];

        if (!empty($alternateUrlsData[$item->getId()])) {
            $storeUrls = $alternateUrlsData[$item->getId()]['hreflangUrls'];

            if (count($storeUrls) > 1) {
                foreach ($storeUrls as $storeId => $altUrl) {
                    if (!empty($altCodes[$storeId])) {
                        $alternateUrls[$altCodes[$storeId]] = $altUrl;
                    }
                }
            }
        }

        return array_unique($alternateUrls);
    }

    /**
     * @return bool
     */
    public function getIsAllowedImages()
    {
        return $this->helper->isProductImages(); //AIzaSyDml8OFxUAnA3R0lhpio3HarZUecqosIX0
    }

    /**
     * @return bool
     */
    public function getIsAllowedVideo()
    {
        return $this->helper->isProductVideos();
    }

    /**
     * @param Magento\Framework\DataObject $item
     * @return string
     */
    protected function getItemUrl($item)
    {
        if (strpos(trim($item->getUrl()), 'http') === 0) {
            $url = $item->getUrl();
        } else {
            $url = $this->storeBaseUrl . $item->getUrl();
        }

        return $this->helper->trailingSlash($url);
    }
}
