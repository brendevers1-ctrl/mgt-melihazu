<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model;

/**
 * @api
 */
interface LayeredFiltersProviderInterface
{
    /**
     * Retrieve data
     *
     * @return array
     */
    public function getCurrentLayeredFilters();
}
