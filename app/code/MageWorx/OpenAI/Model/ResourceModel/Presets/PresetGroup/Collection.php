<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\Presets\PresetGroup;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageWorx\OpenAI\Model\Presets\PresetGroup as Model;
use MageWorx\OpenAI\Model\ResourceModel\Presets\PresetGroup as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
