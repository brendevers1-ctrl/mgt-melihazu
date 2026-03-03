<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Model\Source;

/**
 * Used in creating options for config value selection
 *
 */
class SellerPages extends \MageWorx\SeoMarkup\Model\Source
{
    const ALL_PAGES = 'all';
    const HOME_PAGE = 'home';

    /**
     *
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ALL_PAGES,
                'label' => __('All Pages')
            ],
            [
                'value' => self::HOME_PAGE,
                'label' => __('Home Page')
            ],
        ];
    }
}
