<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;

class Delete extends SitemapController
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        try {
            $sitemap = $this->initSitemap();

            if (!$sitemap->getId()) {
                $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to delete.'));
                $this->_redirect('mageworx_xmlsitemap/*/');
            }

            $filename = $sitemap->getSitemapFilename();
            $this->sitemapResource->delete($sitemap);
            $this->messageManager->addSuccessMessage(__('The "%1" sitemap has been deleted.', $filename));
            $this->_redirect('mageworx_xmlsitemap/*/');

        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('mageworx_xmlsitemap/*/index');
        }
    }
}