<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Observer;

use Exception;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use MageWorx\XmlSitemap\Helper\MagentoSitemap;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;

/**
 * Observer class for product template apply proccess
 */
class GenerateSitemap implements ObserverInterface
{

    /**
     * @var DateTime
     */
    protected $date;

    /**
     *
     * @var CollectionFactory
     */
    protected $sitemapCollectionFactory;

    /**
     * @var MagentoSitemap;
     */
    protected $helperMagentoSitemap;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * GenerateSitemap constructor.
     *
     * @param DateTime $date
     * @param CollectionFactory $sitemapCollectionFactory
     * @param MagentoSitemap $helperMagentoSitemap
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        DateTime $date,
        CollectionFactory $sitemapCollectionFactory,
        MagentoSitemap $helperMagentoSitemap,
        State $state,
        TransportBuilder $transportBuilder
    ) {

        $this->date                     = $date;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->helperMagentoSitemap     = $helperMagentoSitemap;
        $this->transportBuilder         = $transportBuilder;
    }

    /**
     * Apply product template
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $collection       = $this->sitemapCollectionFactory->create();
        $generationErrors = [];

        if ($ids = $observer->getData('sitemapIds')) {
            $collection->loadByIds($ids);
        }

        foreach ($collection as $sitemap) {
            try {
                $sitemap->generateXml();

            } catch (Exception $e) {
                $generationErrors[] = $e->getMessage();
            }
        }

        if ($generationErrors && $this->helperMagentoSitemap->getErrorRecipient()
        ) {
            $header = $this->getEmailHeader($observer);
            array_unshift($generationErrors, $header);
            $this->transportBuilder->setTemplateIdentifier(
                $this->helperMagentoSitemap->getErrorEmailTemplate()
            )->setTemplateOptions(
                [
                    'area'  => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $generationErrors)]
            )->setFrom(
                $this->helperMagentoSitemap->getErrorIdentity()
            )->addTo(
                $this->helperMagentoSitemap->getErrorRecipient()
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
    }

    protected function getEmailHeader($observer)
    {
        $sitemapIds = $observer->getData('sitemapIds');

        $string = __('Unfortunately, the process of generating sitemap(s) went wrong.');
        $string .= ' ' . __('Module') . ': MageWorx_XmlSitemap, ' . __('Date') . ': ' . date("Y-m-d") . '. ';

        if ($sitemapIds) {
            $string .= __('Sitemap(s) with id(s)  %1 were not updated.', implode(", ", $sitemapIds));
        }

        return $string;
    }
}
