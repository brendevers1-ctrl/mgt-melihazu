<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source;

class BusinessDays extends \MageWorx\SeoMarkup\Model\Source
{
    /**
     * {@inheritDoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'Monday', 'label' => __('Monday')],
            ['value' => 'Tuesday', 'label' => __('Tuesday')],
            ['value' => 'Wednesday', 'label' => __('Wednesday')],
            ['value' => 'Thursday', 'label' => __('Thursday')],
            ['value' => 'Friday', 'label' => __('Friday')],
            ['value' => 'Saturday', 'label' => __('Saturday')],
            ['value' => 'Sunday', 'label' => __('Sunday')]
        ];
    }
}
