<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Link extends Column
{
    /**
     * @return void
     */
    public function prepare()
    {
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['link'] = $this->getLink($item);
            }
        }

        return $dataSource;
    }

    /**
     * Prepare link to display in grid - these data should be generated dynamically
     *
     * @param array $item
     * @return string
     */
    public function getLink($item)
    {
        return htmlspecialchars($item['sitemap_link']);
    }
}
