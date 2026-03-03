<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Controller\Adminhtml\Sitemap;

use Magento\Framework\Phrase;
use MageWorx\XmlSitemap\Model\Sitemap as SitemapModel;


class MassGenerate extends MassAction
{
    /**
     * @param SitemapModel $sitemap
     * @return $this
     */
    protected function doTheAction(SitemapModel $sitemap)
    {
        $sitemap->generateXml();

        return $this;
    }

    /**
     * @param int $count
     * @return Phrase
     */
    protected function getSuccessMessage($count): Phrase
    {
        return __('A total of %1 sitemap(s) have been generated.', $count);
    }

    /**
     * @return Phrase
     */
    protected function getErrorMessage(): Phrase
    {
        return __('An error occurred while generating sitemap(s).');
    }
}
