<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Model\ResourceModel\Cms\PageFactory;
use MageWorx\SeoAll\Helper\Page as PageHelper;
use MageWorx\XmlSitemap\Model\WriterInterface;

/**
 * {@inheritdoc}
 */
class Page extends AbstractGenerator
{
    /**
     * @var
     */
    protected $cmsFactory;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Page constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param PageFactory $cmsFactory
     * @param PageHelper $pageHelper
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        PageFactory $cmsFactory,
        PageHelper $pageHelper,
        EventManagerInterface $eventManager
    ) {
        $this->code         = 'cms';
        $this->name         = __('CMS Pages');
        $this->cmsFactory   = $cmsFactory;
        $this->pageHelper   = $pageHelper;
        $this->eventManager = $eventManager;
        parent::__construct($helper, $objectManager);
    }

    /**
     * @param string $storeId
     * @param WriterInterface $writer
     * @return mixed|void
     */
    public function generate($storeId, $writer)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;

        $changefreq    = $this->helper->getPageChangefreq($storeId);
        $collection    = $this->cmsFactory->create()->getCollection($storeId);
        $this->counter = count($collection);

        foreach ($collection as $item) {
            $isHomePage = $this->pageHelper->getIsHomePage($item->getUrl());

            if ($this->helper->isOptimizeHomePage() && $isHomePage) {
                $item->setUrl('');
                $priority = 1;
            } else {
                $priority = $this->helper->getPagePriority($storeId);
            }

            $altCodes = $alternateUrlsData = [];

            if ($this->helper->useHreflangs()) {

                $container = new \Magento\Framework\DataObject(
                    [
                        'item'                => $item,
                        'store_id'            => $storeId,
                        'alternate_urls_data' => $alternateUrlsData,
                        'alt_codes'           => $altCodes,
                        'is_home_page'        => $isHomePage
                    ]
                );

                /** @see \MageWorx\SeoBase\Model\Observer\XmlSitemap\AddCmsPageHreflangUrlsDataObserver */
                $this->eventManager->dispatch(
                    'mageworx_xmlsitemap_cms_page_hreflang_urls',
                    ['container' => $container]
                );

                $alternateUrlsData = $container->getData('alternate_urls_data');
                $altCodes          = $container->getData('alt_codes');
            }

            $alternateUrls = $this->getAlternateUrls($alternateUrlsData, $item, $altCodes);

            if ($alternateUrls) {
                $this->counter += count($alternateUrls);
            }

            /**@var \MageWorx\XmlSitemap\Model\Writer $writer */

            $writer->setAlternateUrls($alternateUrls);

            $writer->write(
                $this->getItemUrl($item, $isHomePage),
                $this->helper->getCurrentDate(),
                $changefreq,
                $priority,
                false
            );
        }
        unset($collection);
    }

    /**
     * @param DataObject $item
     * @param bool $isHomePage
     * @return string
     */
    protected function getItemUrl($item, $isHomePage)
    {
        if ($isHomePage) {
            return $this->helper->trailingSlash($this->storeBaseUrl, true);
        }

        return $this->helper->trailingSlash($this->storeBaseUrl . $item->getUrl());
    }

    /**
     * @param array $alternateUrlsData
     * @param DataObject $item
     * @param array $altCodes
     * @return array
     */
    protected function getAlternateUrls($alternateUrlsData, $item, array $altCodes)
    {
        if (!empty($alternateUrlsData[$item->getPageId()]['hreflangUrls'])) {

            foreach ($alternateUrlsData[$item->getPageId()]['hreflangUrls'] as $storeId => $altUrl) {
                $alternateUrlsData[$item->getPageId()]['hreflangUrls'][$altCodes[$storeId]] = $altUrl;
                unset($alternateUrlsData[$item->getPageId()]['hreflangUrls'][$storeId]);
            }

            return $alternateUrlsData[$item->getPageId()]['hreflangUrls'];
        }

        return [];
    }
}
