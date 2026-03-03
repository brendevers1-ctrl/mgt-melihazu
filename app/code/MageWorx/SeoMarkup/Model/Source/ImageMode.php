<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source;

class ImageMode extends \MageWorx\SeoMarkup\Model\Source
{
    const BASE = 'base';
    const ALL = 'all';

    /**
     * {@inheritDoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::BASE, 'label' => __('Base only')],
            ['value' => self::ALL, 'label' => __('All collection')]
        ];
    }
}
