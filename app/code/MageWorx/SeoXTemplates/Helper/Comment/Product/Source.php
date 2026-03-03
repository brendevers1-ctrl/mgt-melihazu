<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoXTemplates\Helper\Comment\Product;

class Source extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Return comment for url variables
     *
     * @return string
     */
    public function getVariablesComment(): string
    {
        return '<p><p><b>' . __('Template variables') . '</b><br>' .
            '<p>[attribute] — e.g. [name], [price], [manufacturer], [color] — '
            . __('will be replaced with the respective product attribute value or removed if value is not available') . '<br>' .
            '<p>[attribute1|attribute2|...] — e.g. [manufacturer|brand] — ' .
            __('if the first attribute value is not available for the product the second will be used and so on untill it finds a value') . '<br>' .
            '<p>[prefix {attribute} suffix] or<br><p>[prefix {attribute1|attribute2|...} suffix] — e.g. [({color} color)] — ' .
            __('if an attribute value is available it will be prepended with prefix and appended with suffix, either prefix or suffix can be used alone') . '.<br>';
    }

    /**
     * Return additional category comment
     *
     * @return string
     */
    public function getAdditionalCategoryComment(): string
    {
        return '<p>' . __('Additional variables available') . ': [category], [categories], [store_name], [website_name]<br>' .
            '<p><font color = "#ea7601">' . __('Note: The variables [category] and [categories] should be used when categories are added in product path only to avoid duplicates in meta tags') . '.</font>';
    }

    /**
     * Return comment for randomizer
     *
     * @return string
     */
    public function getRandomizerComment(): string
    {
        return '<br><p>' . __('Randomizer feature is available. The construction like [Buy||Order||Purchase] will use a randomly picked word for each next item when applying a template.') . '<br>' .
            __('Also randomizers can be used within other template variables, ex: ') . '[for only||for {price}] .' .
            __('Number of randomizers blocks is not limited within the template.') . '<br>';
    }

    /**
     * Return example for meta title
     *
     * @return string
     */
    public function getMetaTitleExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p><p>[name][from||by {manufacturer|brand}][ ({color} color)][ for||for special {price}][ in {categories}] <p>'. __('will be transformed into') .
            '<br><p>HTC Touch Diamond by HTC (Black color) for € 517.50 in Cell Phones - Electronics';
    }

    /**
     * Return example for keywords
     *
     * @return string
     */
    public function getKeywordsExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p><p>[name][, {color} color][, {size} measurements||size][, {category}] <p>' . __('will be transformed into') . '<br>
                    <p>CN Clogs Beach/Garden Clog, Blue color, 10 size, Shoes';
    }

    /**
     * Return example for description
     *
     * @return string
     */
    public function getDescriptionExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p><p>[Buy||Order] [name][ by {manufacturer|brand}][ of {color} color][ for only||for {price}][ in {categories}] at[ {store_name},][ website_name]. [short_description] <p>' .
            __('will be transformed into') .
            '<br><p>Order HTC Touch Diamond by HTC of Black color for only € 517.50 in Cell Phones - Electronics at Digital Store, Digital-Store.com. HTC Touch Diamond signals a giant leap forward in combining hi-tech prowess with intuitive usability and exhilarating design';
    }

    /**
     * Return example for url
     *
     * @return string
     */
    public function getUrlExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p>[name][ by {manufacturer|brand}][ {color} color][ for {price}] <p>' . __('will be transformed into') .
            '<br><p>htc-touch-diamond-by-htc-black-color-for-517-50<br>';
    }

    /**
     * Return example for seo name
     *
     * @return string
     */
    public function getSeoNameExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p>[name][ by {manufacturer|brand}][ of {color} color][ for||for only {price}] <p>' . __('will be transformed into') .
            '<br><p>HTC Touch Diamond by HTC of Black color for only € 517.50<br>';
    }

    /**
     * @return string
     */
    public function getAdditionalGalleryComment(): string
    {
        return '<p>' . __('Additional variable available') . ': [image_position]<br>';
    }

    /**
     * Return example for gallery
     *
     * @return string
     */
    public function getGalleryExample(): string
    {
        return '<p><b>' . __('Example') . '</b><p><p>[name][, {color} color][-{image_position}] <p>' . __('will be transformed into') . '<br>
                    <p>CN Clogs Beach/Garden Clog, Blue color-3';
    }
}
