<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel\Presets\Preset;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageWorx\OpenAI\Model\Presets\Preset as Model;
use MageWorx\OpenAI\Model\ResourceModel\Presets\Preset as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $additionalData = ['content' => 'content'];
        $options = $this->_toOptionArray('code', 'name', $additionalData);

        // Add additional data to options as params
        foreach ($options as &$option) {
            foreach ($additionalData as $key => $value) {
                $option['params']['data-' . $key] = $option[$key];
                unset($option[$key]);
            }
        }

        return $options;
    }
}
