<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\Validator\StringLength;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;
use MageWorx\XmlSitemap\Model\SitemapFactory as SitemapFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Backend\App\Action\Context;
use MageWorx\XmlSitemap\Model\Source\EntityType;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use MageWorx\XmlSitemap\Helper\MagentoSitemap as MagentoSitemapHelper;
use RuntimeException;


class Save extends SitemapController
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var JsHelper
     */
    protected $jsHelper;

    /**
     * @var StringLength
     */
    protected $stringValidator;

    /**
     * @var AvailablePath
     */
    protected $pathValidator;

    /**
     * @var MagentoSitemapHelper
     */
    protected $helperMagentoSitemap;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param JsHelper $jsHelper
     * @param Registry $registry
     * @param SitemapFactory $sitemapFactory
     * @param SitemapResourceInterface $sitemapResource
     * @param StringLength $stringValidator
     * @param AvailablePath $pathValidator
     */
    public function __construct(
        Context $context,
        DateTime $date,
        JsHelper $jsHelper,
        Registry $registry,
        SitemapFactory $sitemapFactory,
        SitemapResourceInterface $sitemapResource,
        StringLength $stringValidator,
        AvailablePath $pathValidator,
        MagentoSitemapHelper $helperMagentoSitemap
    ) {
        $this->date                 = $date;
        $this->jsHelper             = $jsHelper;
        $this->stringValidator      = $stringValidator;
        $this->pathValidator        = $pathValidator;
        $this->helperMagentoSitemap = $helperMagentoSitemap;
        parent::__construct($context, $registry, $sitemapFactory, $sitemapResource);
    }

    /**
     * run the action
     *
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data['server_path']  = $this->getRequest()->getPost('server_path');
        $data['sitemap_path'] = $this->getRequest()->getPost('sitemap_path');
        $data['store_id']     = $this->getRequest()->getPost('store_id');
        $sitemapFileName      = $this->getRequest()->getPost('sitemap_filename');

        if (empty($data['sitemap_path']) || empty($sitemapFileName) || empty($data['store_id'])) {

            $this->messageManager->addErrorMessage(
                __('Incorrect data.')
            );

            return $resultRedirect->setPath('mageworx_xmlsitemap/*/');
        }

        $entityTypes = $this->getSortedEntities();

        foreach ($entityTypes as $entityType) {
            $sitemap  = $this->initSitemap();
            $filename = $sitemap->getId()
                ? $sitemapFileName
                : $this->getPreparedFileName($sitemapFileName, $entityType);

            $data['entity_type']      = $entityType;
            $data['sitemap_filename'] = $filename;

            $originalSitemap = $sitemap->getId() ? clone $sitemap : null;
            $sitemap->addData($data);

            $this->_eventManager->dispatch(
                'mageworx_xmlsitemaps_sitemap_prepare_save',
                [
                    'sitemap' => $sitemap,
                    'request' => $this->getRequest()
                ]
            );

            try {
                $this->sitemapResource->save($sitemap);

                //we delete old files after validation new params
                if ($originalSitemap) {
                    $originalSitemap->removeFiles();
                    $originalSitemap = null;
                }

                $this->messageManager->addSuccessMessage(
                    __('Sitemap %1 was successfully saved', $sitemap->getSitemapFilename())
                );

                if ($this->getRequest()->getParam('generate')) {
                    $sitemap->generateXml();
                }

                continue;

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the sitemap.')
                );
            }

            return $resultRedirect->setPath('mageworx_xmlsitemap/*/');
        }

        return $resultRedirect->setPath('mageworx_xmlsitemap/*/');
    }

    /**
     * @param string $filename
     * @param string $entityType
     * @return string
     */
    protected function getPreparedFileName($filename, $entityType)
    {
        if ($entityType == EntityType::DEFAULT_TYPE) {
            return $filename;
        }

        if (preg_match('#\.xml$#', $filename)) {
            $filename = str_replace('.xml', '', $filename);
        }

        return $filename . "_" . $entityType;
    }

    /**
     * @return array
     */
    protected function getSortedEntities()
    {
        $entityTypes = $this->getRequest()->getPost('entity_type', [EntityType::DEFAULT_TYPE]);

        //The first element must have the largest length for fast validation by filename length
        usort(
            $entityTypes,
            function ($a, $b) {
                return strlen($b) - strlen($a);
            }
        );

        return $entityTypes;
    }
}
