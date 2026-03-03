<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Model\ResourceModel\Catalog\CategoryFactory;
use MageWorx\XmlSitemap\Model\WriterInterface;
use Zend_Db_Statement_Exception;

/**
 * {@inheritdoc}
 */
class Category extends \MageWorx\XmlSitemap\Model\Generator\AbstractMediaGenerator
{
    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Category constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param CategoryFactory $categoryFactory
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        CategoryFactory $categoryFactory,
        EventManagerInterface $eventManager
    ) {
        $this->code            = 'category';
        $this->name            = __('Categories');
        $this->categoryFactory = $categoryFactory;
        parent::__construct($helper, $objectManager);
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

        $changefreq = $this->helper->getCategoryChangefreq($storeId);
        $priority   = $this->helper->getCategoryPriority($storeId);

        $this->counter = 0;
        $categoryModel = $this->categoryFactory->create();

        while (!$categoryModel->isCollectionReaded()) {
            $collection = $categoryModel->getLimitedCollection(
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

                /** @see \MageWorx\SeoBase\Model\Observer\XmlSitemap\AddCategoryHreflangUrlsDataObserver */
                $this->eventManager->dispatch(
                    'mageworx_xmlsitemap_category_hreflang_urls',
                    ['container' => $container]
                );

                $alternateUrlsData = $container->getData('alternate_urls_data');
                $altCodes          = $container->getData('alt_codes');
            }

            foreach ($collection as $item) {

                $images = $this->getIsAllowedImages() ? $item->getImages() : false;

                if ($images) {
                    $count = count($images->getCollection());

                    if ($count) {
                        $this->imageCounter += $count + 1;
                    }
                }

                $alternateUrls = $this->getAlternateUrls($alternateUrlsData, $item, $altCodes);

                if ($alternateUrls) {
                    $this->counter += count($alternateUrls);
                }

                /**@var \MageWorx\XmlSitemap\Model\Writer $writer */

                $writer->setAlternateUrls($alternateUrls);

                $writer->write(
                    $this->getItemUrl($item),
                    $this->getItemChangeDate($item),
                    $changefreq,
                    $priority,
                    $images
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
        return $this->helper->isCategoryImages();
    }

    /**
     * @param DataObject $item
     * @return string
     */
    protected function getItemUrl($item)
    {
        $url = $this->storeBaseUrl . $item->getUrl();

        return $this->helper->trailingSlash($url);
    }
}