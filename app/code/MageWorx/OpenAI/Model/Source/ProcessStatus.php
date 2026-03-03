<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;

class ProcessStatus implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => QueueProcessInterface::STATUS_ENABLED,
                'label' => __('Enabled')
            ],
            [
                'value' => QueueProcessInterface::STATUS_DISABLED,
                'label' => __('Disabled')
            ]
        ];
    }
}
