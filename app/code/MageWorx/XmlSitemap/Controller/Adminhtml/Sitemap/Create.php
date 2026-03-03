<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;
use Magento\Backend\App\Action\Context;
use MageWorx\XmlSitemap\Model\SitemapFactory as SitemapFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;

class Create extends SitemapController
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Create constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param SitemapFactory $sitemapFactory
     * @param SitemapResourceInterface $sitemapResource
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SitemapFactory $sitemapFactory,
        SitemapResourceInterface $sitemapResource,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $registry, $sitemapFactory, $sitemapResource);
    }

    /**
     * Create product sitemap
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(__('Sitemap'), __('Sitemap'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Sitemap'));

        return $resultPage;
    }
}
