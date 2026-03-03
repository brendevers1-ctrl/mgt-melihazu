<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Source;

use MageWorx\SeoAll\Model\Source;

class ProductImageSource extends Source
{
    const ORIGINAL_SOURCE = 'original';
    const CACHE_SOURCE    = 'cache';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CACHE_SOURCE,
                'label' => __('Cache')
            ],
            [
                'value' => self::ORIGINAL_SOURCE,
                'label' => __('Original')
            ],
        ];
    }
}
