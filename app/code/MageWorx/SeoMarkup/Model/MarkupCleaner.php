<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Model;


class MarkupCleaner
{
    /**
     * @param string $html
     * @return string
     */
    public function removeOfferMarkup($html)
    {
        return str_replace(
            ['itemprop="offers"', 'itemscope', 'itemtype="http://schema.org/Offer"'],
            '',
            $html
        );
    }
}
