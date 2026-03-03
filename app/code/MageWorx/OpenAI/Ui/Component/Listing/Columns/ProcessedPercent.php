<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class ProcessedPercent extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['size']) && isset($item['processed']) && $item['size'] > 0) {
                    $percent                      = ($item['processed'] / $item['size']) * 100;
                    $item[$this->getData('name')] = round($percent, 2) . '%';
                } else {
                    $item[$this->getData('name')] = __('N/A');
                }
            }
        }

        return $dataSource;
    }
}
