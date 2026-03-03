<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\Source\Hreflangs;

use MageWorx\SeoBase\Model\Source;

class PageTypes extends Source
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * PageTypes constructor.
     *
     * @param array $types
     */
    public function __construct(array $types = [])
    {
        foreach ($types as $code => $label) {
            $this->types[$code] = __($label);
        }
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->types as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }
}
