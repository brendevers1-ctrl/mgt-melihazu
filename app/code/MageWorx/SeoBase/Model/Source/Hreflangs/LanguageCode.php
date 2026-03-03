<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Source\Hreflangs;

use MageWorx\SeoBase\Model\Source\Locale;

class LanguageCode extends Locale
{
    const USE_CONFIG = 'use_config';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => self::USE_CONFIG, 'label' => 'Use config']];

        return array_merge($options, parent::toOptionArray());
    }
}
