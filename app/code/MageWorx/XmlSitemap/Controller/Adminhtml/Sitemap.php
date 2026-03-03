<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use MageWorx\XmlSitemap\Model\SitemapFactory as SitemapFactory;
use Magento\Framework\Registry;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;

abstract class Sitemap extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_XmlSitemap::sitemap';

    /**
     * Sitemap factory
     *
     * @var sitemapFactory
     */
    protected $sitemapFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var SitemapResourceInterface
     */
    protected $sitemapResource;

    /**
     * Sitemap constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SitemapFactory $sitemapFactory
     * @param SitemapResourceInterface $sitemapResource
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SitemapFactory $sitemapFactory,
        SitemapResourceInterface $sitemapResource
    ) {
        $this->coreRegistry    = $registry;
        $this->sitemapFactory  = $sitemapFactory;
        $this->sitemapResource = $sitemapResource;
        parent::__construct($context);
    }

    /**
     * @param int|null $sitemapId
     * @return \MageWorx\XmlSitemap\Model\Sitemap
     */
    protected function initSitemap($sitemapId = null)
    {
        $sitemapId = ($sitemapId === null) ? $this->getRequest()->getParam('sitemap_id') : $sitemapId;

        $sitemap = $this->sitemapFactory->create();
        if ($sitemapId) {
            $this->sitemapResource->load($sitemap, $sitemapId);
        }

        //graceful set to true for the split sitemap generation
        $this->coreRegistry->register('mageworx_xmlsitemap_sitemap', $sitemap, true);

        return $sitemap;
    }

    /**
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('MageWorx_XmlSitemap::sitemap')
                   ->addBreadcrumb(__('XML Sitemap by MageWorx'), __('XML Sitemap by MageWorx'));

        return $resultPage;
    }
}