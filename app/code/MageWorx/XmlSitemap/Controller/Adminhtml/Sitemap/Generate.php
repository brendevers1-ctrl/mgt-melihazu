<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap as SitemapController;


class Generate extends SitemapController
{
    /**
     * Generate sitemap
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $sitemap = $this->initSitemap();

        if ($sitemap->getSitemapId()) {
            try {
                $sitemap->generateXml();

                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        $this->_redirect('mageworx_xmlsitemap/*/');
    }
}
