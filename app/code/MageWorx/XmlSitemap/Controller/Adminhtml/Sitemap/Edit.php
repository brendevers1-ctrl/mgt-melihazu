<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;
use Magento\Backend\App\Action\Context;
use MageWorx\XmlSitemap\Model\SitemapFactory as SitemapFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;

class Edit extends SitemapController
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param SitemapResourceInterface $sitemapResource
     * @param SitemapFactory $sitemapFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        SitemapResourceInterface $sitemapResource,
        SitemapFactory $sitemapFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $registry, $sitemapFactory, $sitemapResource);
    }

    /**
     * Edit product sitemap
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $sitemapId = $this->getRequest()->getParam('sitemap_id');

        try {
            $sitemap = $this->initSitemap($sitemapId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This sitemap no longer exists.'));
            $this->_redirect('mageworx_xmlsitemap/*/index/');

            return;
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            __('Edit Sitemap'),
            __('Edit Sitemap "%s"', $sitemap->getSitemapFilename())
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Sitemap'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Sitemap "%1"', $sitemap->getSitemapFilename()));

        return $resultPage;
    }
}
