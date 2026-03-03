<?php

declare(strict_types=1);

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductMessageType implements OptionSourceInterface
{
    const TYPE_PRODUCT_META_DESCRIPTION = 'product_meta_description';
    const TYPE_PRODUCT_META_KEYWORDS = 'product_meta_keyword';
    const TYPE_PRODUCT_META_TITLE = 'product_meta_title';
    const TYPE_PRODUCT_SEO_NAME = 'product_seo_name';
    const TYPE_PRODUCT_DESCRIPTION = 'product_description';
    const TYPE_PRODUCT_SHORT_DESCRIPTION = 'product_short_description';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TYPE_PRODUCT_META_DESCRIPTION,
                'label' => __('Product Meta Description')
            ],
            [
                'value' => self::TYPE_PRODUCT_META_KEYWORDS,
                'label' => __('Product Meta Keywords')
            ],
            [
                'value' => self::TYPE_PRODUCT_META_TITLE,
                'label' => __('Product Meta Title')
            ],
            [
                'value' => self::TYPE_PRODUCT_SEO_NAME,
                'label' => __('Product Seo Name')
            ],
            [
                'value' => self::TYPE_PRODUCT_DESCRIPTION,
                'label' => __('Product Description')
            ],
            [
                'value' => self::TYPE_PRODUCT_SHORT_DESCRIPTION,
                'label' => __('Product Short Description')
            ],
        ];
    }
}
