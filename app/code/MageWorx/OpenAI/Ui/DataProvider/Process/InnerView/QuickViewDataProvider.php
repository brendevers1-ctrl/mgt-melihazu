<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\DataProvider\Process\InnerView;

class QuickViewDataProvider extends DataProvider
{
    const LISTING_NAME = 'mageworx_openai_queue_item_quick_view_listing_data_source';

    public function getAllIds(): array
    {
        if ($this->request->getParam('process_id')) {
            return $this->getCollection()->addFieldToFilter('process_id', $this->request->getParam('process_id'))->getAllIds();
        }

        return $this->getCollection()->getAllIds();
    }
}
