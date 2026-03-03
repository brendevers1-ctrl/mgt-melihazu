<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source;

use Magento\Cms\Api\Data\PageInterface;

class PageAttributes extends \MageWorx\SeoMarkup\Model\Source
{
    public function toOptionArray()
    {
        return [
            __('-- Please Select --'),
            PageInterface::TITLE            => __('Page Title (%1)', PageInterface::TITLE),
            PageInterface::CONTENT_HEADING  => __('Content Heading (%1)', PageInterface::CONTENT_HEADING),
            PageInterface::META_TITLE       => __('Meta Title (%1)', PageInterface::META_TITLE),
            PageInterface::META_KEYWORDS    => __('Meta Keywords (%1)', PageInterface::META_KEYWORDS),
            PageInterface::META_DESCRIPTION => __('Meta Description (%1)', PageInterface::META_DESCRIPTION)
        ];
    }
}
