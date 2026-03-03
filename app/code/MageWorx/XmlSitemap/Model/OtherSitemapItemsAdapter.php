<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\XmlSitemap\Model;

class OtherSitemapItemsAdapter extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @param int $storeId
     * @return array
     */
    public function getItems(int $storeId): array
    {
        $this->setStoreId($storeId);
        $this->_sitemapItems = [];
        $this->_initSitemapItems();

        return $this->_sitemapItems;
    }

    /**
     * @return void
     */
    public function collectSitemapItems()
    {
        return;
    }
}