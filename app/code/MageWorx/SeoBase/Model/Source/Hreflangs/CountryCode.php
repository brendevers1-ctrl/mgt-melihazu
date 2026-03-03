<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Source\Hreflangs;

use MageWorx\SeoBase\Model\Source\Country;

class CountryCode extends Country
{
    const DO_NOT_ADD = 'do_not_add';
    const USE_CONFIG = 'use_config';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::USE_CONFIG, 'label' => 'Use config'],
            ['value' => self::DO_NOT_ADD, 'label' => 'Do not add']
        ];

        return array_merge($options, parent::toOptionArray());
    }
}
