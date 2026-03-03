<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\Presets;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MageWorx\OpenAI\Api\Data\PresetGroupInterface;

class PresetGroup extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mageworx_openai_preset_groups', PresetGroupInterface::GROUP_ID);
    }
}
