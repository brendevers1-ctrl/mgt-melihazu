<?php

declare(strict_types=1);

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CategoryMessageType implements OptionSourceInterface
{
    const TYPE_CATEGORY_DESCRIPTION = 'category_description';
    const TYPE_CATEGORY_META_TITLE = 'category_meta_title';
    const TYPE_CATEGORY_META_DESCRIPTION = 'category_meta_description';
    const TYPE_CATEGORY_META_KEYWORDS = 'category_meta_keywords';
    const TYPE_CATEGORY_SEO_NAME = 'category_seo_name';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TYPE_CATEGORY_META_DESCRIPTION,
                'label' => __('Category Meta Description')
            ],
            [
                'value' => self::TYPE_CATEGORY_META_KEYWORDS,
                'label' => __('Category Meta Keywords')
            ],
            [
                'value' => self::TYPE_CATEGORY_META_TITLE,
                'label' => __('Category Meta Title')
            ],
            [
                'value' => self::TYPE_CATEGORY_SEO_NAME,
                'label' => __('Category Seo Name')
            ],
            [
                'value' => self::TYPE_CATEGORY_DESCRIPTION,
                'label' => __('Category Description')
            ]
        ];
    }
}
