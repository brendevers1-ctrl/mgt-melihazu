<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\Presets;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Preset extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mageworx_openai_presets', 'entity_id');
    }
}
