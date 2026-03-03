<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Magento\Framework\Phrase;
use MageWorx\XmlSitemap\Model\Sitemap as SitemapModel;

class MassDelete extends MassAction
{
    /**
     * @param SitemapModel $sitemap
     * @return $this
     */
    protected function doTheAction(SitemapModel $sitemap)
    {
        $this->sitemapResource->delete($sitemap);

        return $this;
    }

    /**
     * @param int $count
     * @return Phrase
     */
    protected function getSuccessMessage($count): Phrase
    {
        return __('A total of %1 record(s) have been deleted', $count);
    }

    /**
     * @return Phrase
     */
    protected function getErrorMessage(): Phrase
    {
        return __('An error occurred while deleting record(s).');
    }
}
