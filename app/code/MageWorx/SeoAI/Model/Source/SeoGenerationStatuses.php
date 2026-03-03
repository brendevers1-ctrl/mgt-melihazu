<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Queue process generation statuses fo SEO AI
 * @see \MageWorx\OpenAI\Model\Source\AllGenerationStatuses
 */
class SeoGenerationStatuses implements OptionSourceInterface
{
    protected $arrayOptions = [];
    protected $options      = [];

    public function toOptionArray(): array
    {
        if (empty($this->options)) {
            $this->options = [
                ['value' => 'product_meta_description', 'label' => __('Product: Generate Meta Description')],
                ['value' => 'product_meta_keyword', 'label' => __('Product: Generate Meta Keywords')],
                ['value' => 'product_meta_title', 'label' => __('Product: Generate Meta Title')],
                ['value' => 'product_seo_name', 'label' => __('Product: Generate SEO Name')],
                ['value' => 'product_description', 'label' => __('Product: Generate Description')],
                ['value' => 'product_short_description', 'label' => __('Product: Generate Short Description')],
                ['value' => 'product_improve_short_description', 'label' => __('Product: Improve Short Description')],
                ['value' => 'product_improve_meta_description', 'label' => __('Product: Improve Meta Description')],
                ['value' => 'product_improve_meta_keyword', 'label' => __('Product: Improve Meta Keywords')],
                ['value' => 'product_improve_meta_title', 'label' => __('Product: Improve Meta Title')],
                ['value' => 'product_improve_seo_name', 'label' => __('Product: Improve SEO Name')],
                ['value' => 'product_improve_description', 'label' => __('Product: Improve Description')],
                ['value' => 'category_description', 'label' => __('Category: Generate Description')],
                ['value' => 'category_meta_title', 'label' => __('Category: Generate Meta Title')],
                ['value' => 'category_meta_description', 'label' => __('Category: Generate Meta Description')],
                ['value' => 'category_meta_keywords', 'label' => __('Category: Generate Meta Keywords')],
                ['value' => 'category_seo_name', 'label' => __('Category: Generate SEO Name')],
                ['value' => 'category_improve_description', 'label' => __('Category: Improve Description')],
                ['value' => 'category_improve_meta_title', 'label' => __('Category: Improve Meta Title')],
                ['value' => 'category_improve_meta_description', 'label' => __('Category: Improve Meta Description')],
                ['value' => 'category_improve_meta_keywords', 'label' => __('Category: Improve Meta Keywords')],
                ['value' => 'category_improve_seo_name', 'label' => __('Category: Improve SEO Name')]
            ];
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->arrayOptions)) {
            $options = $this->toOptionArray();

            // Create array with key => value
            $this->arrayOptions = [];
            foreach ($options as $option) {
                $this->arrayOptions[$option['value']] = $option['label'];
            }
        }

        return $this->arrayOptions;
    }

    /**
     * @param string $value
     * @return string
     */
    public function getLabelByValue(string $value): string
    {
        $options = $this->toArray();
        return (string)$options[$value] ?: '';
    }
}
