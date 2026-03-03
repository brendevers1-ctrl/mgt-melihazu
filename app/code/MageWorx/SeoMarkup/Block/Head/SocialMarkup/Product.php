<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;
use MageWorx\SeoMarkup\Model\TwitterCardsConfigProvider;

class Product extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup
{
    const IN_STOCK     = 'in stock';
    const OUT_OF_STOCK = 'out of stock';

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperDataProvider;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Product constructor.
     *
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Product $helperDataProvider
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     * @param TwitterCardsConfigProvider $twCardsConfigProvider
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\DataProvider\Product $helperDataProvider,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        OpenGraphConfigProvider $openGraphConfigProvider,
        TwitterCardsConfigProvider $twCardsConfigProvider,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->helperDataProvider = $helperDataProvider;
        $this->priceCurrency      = $priceCurrency;
        parent::__construct(
            $registry,
            $helperWebsite,
            $context,
            $openGraphConfigProvider,
            $twCardsConfigProvider,
            $data,
            $seoFeaturesStatusProvider
        );
    }

    /**
     * @return string
     */
    protected function getMarkupHtml(): string
    {
        if (!$this->openGraphConfigProvider->isEnabledForProduct() &&
            !$this->twCardsConfigProvider->isEnabledForProduct()
        ) {
            return '';
        }

        return $this->getSocialProductInfo();
    }

    /**
     * @return string
     */
    protected function getSocialProductInfo(): string
    {
        $product = $this->getEntity();

        if (!$product) {
            $product = $this->registry->registry('current_product');
        }

        if (!is_object($product)) {
            return '';
        }

        $html         = '';
        $siteName     = $this->escapeHtml($this->helperWebsite->getName());
        $url          = $this->renderUrl(
            $this->escapeHtml($this->helperDataProvider->getProductCanonicalUrl($product))
        );
        $color        = $this->escapeHtml($this->helperDataProvider->getColorValue($product));
        $categoryName = $this->escapeHtml($this->helperDataProvider->getCategoryValue($product));
        $availability = $this->getAvailability($product);
        $productImage = $this->helperDataProvider->getProductImage($product);
        $imageUrl     = $productImage->getImageUrl();
        $productImages = [];

        if ($this->openGraphConfigProvider->isEnabledForProduct()) {

            $brand = $this->helperDataProvider->getBrandValue($product);
            if (!$brand) {
                $brand = $this->helperDataProvider->getManufacturerValue($product);
            }

            $weightString = $this->helperDataProvider->getWeightValue($product);

            if (is_string($weightString)) {
                $weightSep = strpos($weightString, ' ');

                if ($weightSep !== false) {
                    $weightValue = substr($weightString, 0, $weightSep);
                    $weightUnits = $this->convertWeightUnits(substr($weightString, $weightSep + 1));
                }
            }

            $price     = $this->getPrice($product);
            $currency  = strtoupper($this->helperDataProvider->getCurrentCurrencyCode());
            $condition = $this->getCondition($product);

            $html .= "\n";
            $html .= "<meta property=\"og:type\" content=\"product.item\"/>\n";
            $html .= "<meta property=\"og:title\" content=\"" . $this->getTitleForOpenGraph($product) . "\"/>\n";
            $html .= "<meta property=\"og:description\" content=\"" . $this->getDescriptionForOpenGraph($product)
                . "\"/>\n";
            $html .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";

            if (!empty($price)) {
                $html .= "<meta property=\"product:price:amount\" content=\"" . $price . "\"/>\n";

                if ($currency) {
                    $html .= "<meta property=\"product:price:currency\" content=\"" . $currency . "\"/>\n";
                }
            }
            if($this->openGraphConfigProvider->getImageMode() === \MageWorx\SeoMarkup\Model\Source\ImageMode::ALL) {
                $productImages = $this->helperDataProvider->getProductImages($product, true);

                foreach ($productImages as $image) {
                    $imageWidth  = $image->getWidth() ?? 0;
                    $imageHeight = $image->getHeight() ?? 0;

                    $html .= "<meta property=\"og:image\" content=\"" . $imageUrl . "\"/>\n";
                    if($imageWidth !== 0 && $imageHeight !== 0) {
                        $html .= "<meta property=\"og:image:width\" content=\"" . $imageWidth . "\"/>\n";
                        $html .= "<meta property=\"og:image:height\" content=\"" . $imageHeight . "\"/>\n";
                    }
                }
            } else {
                $imageWidth  = $productImage->getWidth();
                $imageHeight = $productImage->getHeight();

                $html .= "<meta property=\"og:image\" content=\"" . $imageUrl . "\"/>\n";
                $html .= "<meta property=\"og:image:width\" content=\"" . $imageWidth . "\"/>\n";
                $html .= "<meta property=\"og:image:height\" content=\"" . $imageHeight . "\"/>\n";
            }

            if ($appId = $this->helperWebsite->getFacebookAppId()) {
                $html .= "<meta property=\"fb:app_id\" content=\"" . $appId . "\"/>\n";
            }

            if ($retailerItemId = $this->helperDataProvider->getProductIdValue($product)) {
                $html .= "<meta property=\"product:retailer_item_id\" content=\"" . $retailerItemId . "\"/>\n";
            }

            if ($color) {
                $html .= "<meta property=\"product:color\" content=\"" . $color . "\"/>\n";
            }

            if ($brand) {
                $html .= "<meta property=\"product:brand\" content=\"" . $brand . "\"/>\n";
            }

            if ($siteName) {
                $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
            }

            if (!empty($weightValue) && !empty($weightUnits)) {
                $html .= "<meta property=\"product:weight:value\" content=\"" . $weightValue . "\"/>\n";
                $html .= "<meta property=\"product:weight:units\" content=\"" . $weightUnits . "\"/>\n";
            }

            if ($categoryName) {
                $html .= "<meta property=\"product:category\" content=\"" . $categoryName . "\"/>\n";
            }

            $html .= "<meta property=\"product:availability\" content=\"" . $availability . "\"/>\n";

            if ($condition) {
                $html .= "<meta property=\"product:condition\" content=\"" . $condition . "\"/>\n";
            }
        }

        if ($this->twCardsConfigProvider->isEnabledForProduct()) {
            $twitterUsername = $this->twCardsConfigProvider->getUsername();
            if ($twitterUsername) {
                $html = $html ? $html : "\n";
                $html .= "<meta name=\"twitter:site\" content=\"" . $twitterUsername . "\"/>\n";
                $html .= "<meta name=\"twitter:creator\" content=\"" . $twitterUsername . "\"/>\n";
                $html .= "<meta name=\"twitter:card\" content=\"summary\"/>\n";
                $html .= "<meta name=\"twitter:title\" content=\"" . $this->getTitleForTwitterCards($product)
                    . "\"/>\n";
                $html .= "<meta name=\"twitter:description\" content=\""
                    . $this->getDescriptionForTwitterCards($product) . "\"/>\n";
                $html .= "<meta name=\"twitter:image\" content=\"" . $imageUrl . "\"/>\n";
                $html .= "<meta name=\"twitter:url\" content=\"" . $url . "\"/>\n";

                if (!empty($price)) {
                    $html .= "<meta name=\"twitter:label1\" content=\"Price\"/>\n";
                    $html .= "<meta name=\"twitter:data1\" content=\"" . $price . "\"/>\n";
                }

                $html .= "<meta name=\"twitter:label2\" content=\"Availability\"/>\n";
                $html .= "<meta name=\"twitter:data2\" content=\"" . $availability . "\"/>\n";
            }
        }

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product
     * @return float
     */
    protected function getPrice($product)
    {
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->__toString();

        if ($price) {
            $price = $this->priceCurrency->round($price);
        }

        return $price;
    }

    protected function getCondition($product)
    {
        $condition = $this->helperDataProvider->getConditionValue($product);
        if ($condition) {
            $ogEnum = [
                'NewCondition'         => 'new',
                'UsedCondition'        => 'used',
                'RefurbishedCondition' => 'refurbished',
                'DamagedCondition'     => 'used'
            ];
            if (!empty($ogEnum[$condition])) {
                return $ogEnum[$condition];
            }
        }

        return '';
    }

    protected function getAvailability($product)
    {
        if ($this->helperDataProvider->getAvailability($product)) {
            return self::IN_STOCK;
        }

        return self::OUT_OF_STOCK;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    protected function convertWeightUnits($value)
    {
        if (strtolower($value) == 'lbs') {
            return 'lb';
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getTitleForOpenGraph(\Magento\Catalog\Model\Product $product): string
    {
        $code  = $this->openGraphConfigProvider->getProductTitleCode();
        $title = '';

        if ($code) {
            $title = strip_tags($this->helperDataProvider->getAttributeValueByCode($product, $code));
        }

        if (!$title) {
            $title = $product->getName();
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getDescriptionForOpenGraph(\Magento\Catalog\Model\Product $product): string
    {
        $code        = $this->openGraphConfigProvider->getProductDescriptionCode();
        $description = '';

        if ($code) {
            $description = $this->helperDataProvider->getAttributeValueByCode($product, $code);
        }

        if (!$description) {
            $description = $product->getShortDescription();
        }

        if (!$description) {
            $description = $product->getDescription();
        }

        $description = $description ? $this->escapeHtmlAttr(strip_tags($description)) : '';

        if ($description && $this->openGraphConfigProvider->getCropProductDescription()) {
            $description = $this->getCroppedDescription(
                $description,
                $this->openGraphConfigProvider->getCropProductDescription()
            );
        }

        return $description;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getTitleForTwitterCards(\Magento\Catalog\Model\Product $product): string
    {
        $code  = $this->twCardsConfigProvider->getProductTitleCode();
        $title = '';

        if ($code) {
            $title = strip_tags($this->helperDataProvider->getAttributeValueByCode($product, $code));
        }

        if (!$title) {
            $title = $product->getName();
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getDescriptionForTwitterCards(\Magento\Catalog\Model\Product $product): string
    {
        $code        = $this->twCardsConfigProvider->getProductDescriptionCode();
        $description = '';

        if ($code) {
            $description = $this->helperDataProvider->getAttributeValueByCode($product, $code);
        }

        if (!$description) {
            $description = $product->getShortDescription();
        }

        if (!$description) {
            $description = $product->getDescription();
        }

        $description = $description ? $this->escapeHtmlAttr(strip_tags($description)) : '';

        if ($description && $this->twCardsConfigProvider->getCropProductDescription()) {
            $description = $this->getCroppedDescription(
                $description,
                $this->twCardsConfigProvider->getCropProductDescription()
            );
        }

        return $description;
    }
}
