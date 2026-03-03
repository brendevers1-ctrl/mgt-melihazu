<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;

class QueueItemStatus implements OptionSourceInterface
{
    /**
     * Get array of options for queue item statuses
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => QueueItemInterface::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => QueueItemInterface::STATUS_PROCESSING, 'label' => __('Processing')],
            ['value' => QueueItemInterface::STATUS_READY, 'label' => __('Ready')],
            ['value' => QueueItemInterface::STATUS_COMPLETED, 'label' => __('Completed')],
            ['value' => QueueItemInterface::STATUS_FAILED, 'label' => __('Failed')],
            ['value' => QueueItemInterface::STATUS_CALLBACK_FAILED, 'label' => __('Callback Failed')],
            ['value' => QueueItemInterface::STATUS_FATAL_ERROR, 'label' => __('Fatal Error')],
            ['value' => QueueItemInterface::STATUS_LOCKED, 'label' => __('Locked')],
            ['value' => QueueItemInterface::STATUS_DENIED, 'label' => __('Denied')],
            ['value' => QueueItemInterface::STATUS_PENDING_REVIEW, 'label' => __('Pending Review')]
        ];
    }
}
