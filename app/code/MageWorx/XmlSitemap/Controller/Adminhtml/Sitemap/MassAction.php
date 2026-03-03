<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use MageWorx\XmlSitemap\Model\SitemapFactory as SitemapFactory;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;
use MageWorx\XmlSitemap\Model\Sitemap as SitemapModel;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;

abstract class MassAction extends SitemapController
{
    /**
     *
     * @var Filter
     */
    protected $filter;

    /**
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassAction constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SitemapFactory $sitemapFactory
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SitemapFactory $sitemapFactory,
        SitemapResourceInterface $sitemapResource,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter            = $filter;
        parent::__construct($context, $registry, $sitemapFactory, $sitemapResource);
    }

    /**
     *
     * @param SitemapModel $sitemap
     * @return mixed
     */
    abstract protected function doTheAction(SitemapModel $sitemap);

    /**
     * Execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $collection     = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $sitemap) {
                $this->doTheAction($sitemap);
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $this->getErrorMessage());
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('mageworx_xmlsitemap/*/index');

        return $redirectResult;
    }

    /**
     * @param int $count
     * @return Phrase
     */
    protected function getSuccessMessage($count): Phrase
    {
        return __('Mass Action successful on %1 records', $count);
    }

    /**
     * @return Phrase
     */
    protected function getErrorMessage()
    {
        return __('Mass Action failed.');
    }
}
