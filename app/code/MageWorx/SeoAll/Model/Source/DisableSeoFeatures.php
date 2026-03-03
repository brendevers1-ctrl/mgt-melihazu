<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model\Source;

use MageWorx\SeoAll\Model\Source;

/**
 * Class DisableSeoFeatures
 */
class DisableSeoFeatures extends Source
{
    /**
     * @var array
     */
    protected $options;

    /**
     * DisableSeoFeatures constructor.
     *
     * @param array $options
     */
    public function __construct(
        array $options = []
    ) {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->options;
    }
}
