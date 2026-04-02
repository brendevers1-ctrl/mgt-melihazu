<?php
/**
 * Copyright © 2019 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Helper\DataProvider;

use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Image;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\ObjectManager;
use MageWorx\SeoMarkup\Model\Source\MerchantReturnPolicy\Fees as MerchantReturnPolicyFees;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByIdInterface;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCHEMA_ORG_URL = 'https://schema.org/';
    const IMAGE_DISPLAY_AREA = 'product_page_image_large';

    protected UrlBuilder $imageUrlBuilder;
    protected GetByIdInterface $getById;
    protected GalleryReadHandler $galleryReadHandler;
    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory
     */
    protected $ratingVoteCollectionFactory;

    /**
     * @var array|null
     */
    protected $ratingData;

    /**
     * @var null|string
     */
    protected $categoryName;

    /**
     * @var array
     */
    protected $attributeValues = [];

    /**
     * @var string
     */
    protected $conditionValue;

    /**
     * @var string|null
     */
    protected $productCanonicalUrl;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var \MageWorx\SeoAll\Helper\MagentoVersion
     */
    protected $helperVersion;

    /**
     * Product constructor.
     *
     * @param \MageWorx\SeoMarkup\Helper\Product $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param UrlBuilder $urlBuilder
     * @param GetByIdInterface $getById
     * @param GalleryReadHandler $galleryReadHandler
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $ratingVoteCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TimezoneInterface $timezone
     * @param DateTime $dateTime
     * @param \MageWorx\SeoAll\Helper\MagentoVersion $helperVersion
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\Product $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        UrlBuilder $urlBuilder,
        GetByIdInterface $getById,
        GalleryReadHandler $galleryReadHandler,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $ratingVoteCollectionFactory,
        \Magento\Framework\App\Helper\Context                                    $context,
        TimezoneInterface                                                        $timezone,
        DateTime                                                                 $dateTime,
        \MageWorx\SeoAll\Helper\MagentoVersion                                   $helperVersion
    ) {
        $this->helperData                  = $helperData;
        $this->storeManager                = $storeManager;
        $this->imageBuilder                = $imageBuilder;
        $this->imageUrlBuilder             = $urlBuilder;
        $this->getById                     = $getById;
        $this->galleryReadHandler          = $galleryReadHandler;
        $this->registry                    = $registry;
        $this->resourceCategory            = $resourceCategory;
        $this->reviewFactory               = $reviewFactory;
        $this->reviewCollectionFactory     = $reviewCollectionFactory;
        $this->ratingVoteCollectionFactory = $ratingVoteCollectionFactory;
        $this->timezone                    = $timezone;
        $this->dateTime                    = $dateTime;
        $this->helperVersion               = $helperVersion;
        parent::__construct($context);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCurrentCurrencyCode(): string
    {
        return (string)$this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getDescriptionValue($product)
    {
        $attributeCode = $this->helperData->getDescriptionCode();

        if ($attributeCode) {
            $description = $this->getAttributeValueByCode($product, $attributeCode);
        } else {
            $description = (string)$product->getShortDescription();
        }

        if ($this->helperData->getIsCropHtmlInDescription()) {
            $description = $this->cropHtmlTags($description);
        }

        return $description;
    }

    /**
     * @param string|null $description
     * @return string
     */
    public function cropHtmlTags(?string $description = ''): string
    {
        if (empty($description)) {
            return '';
        }

        // Removing the <style> tags with content
        $description = preg_replace("~<style.*?>.*?</style>~is", '', $description);

        // Removing another HTML-tags, keep content
        $description = strip_tags($description);

        // Removing extra spaces and line breaks
        $description = trim(preg_replace('/\s+/', ' ', $description));

        return $description;
    }

    /**
     * Retrieve attribute value by attribute code
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return string|array
     */
    public function getAttributeValueByCode($product, $attributeCode)
    {
        if (!empty($this->attributeValues[$product->getId()])
            && array_key_exists($attributeCode, $this->attributeValues[$product->getId()])
        ) {
            return $this->attributeValues[$product->getId()][$attributeCode];
        }

        $value = $product->getData($attributeCode);

        $tempValue = '';

        if (!is_array($value)) {
            $attribute = $product->getResource()->getAttribute($attributeCode);

            if ($attribute && $attribute->usesSource()) {
                $attribute->setStoreId($product->getStoreId());
                $tempValue = $attribute->setStoreId($product->getStoreId())->getSource()->getOptionText($value);
            }
        }

        if ($tempValue) {
            $value = $tempValue;
        }

        if (!$value) {
            if ($product->getTypeId() == 'configurable') {
                $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

                $attributeOptions = [];
                foreach ($productAttributeOptions as $productAttribute) {
                    if ($productAttribute['attribute_code'] != $attributeCode) {
                        continue;
                    }
                    foreach ($productAttribute['values'] as $attribute) {
                        $attributeOptions[] = $attribute['store_label'];
                    }
                }
                if (count($attributeOptions) == 1) {
                    $value = array_shift($attributeOptions);
                }
            } else {
                $value = $product->getData($attributeCode);
            }
        }

        $finalValue = is_array($value) ? array_map('trim', array_filter($value)) : trim($value ?? '');

        $this->attributeValues[$product->getId()][$attributeCode] = $finalValue;

        return $finalValue;
    }

    /**
     * @return string
     * @todo Retrieve product canonical URL from SeoBase or Magento Canonical URL.
     */
    public function getProductCanonicalUrl($product)
    {
        if (!empty($this->productCanonicalUrl)) {
            return $this->productCanonicalUrl;
        }
        $this->productCanonicalUrl = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);

        return $this->productCanonicalUrl;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getShippingDetailsData(\Magento\Catalog\Model\Product $product): array
    {
        if ($product->isVirtual()) {
            return [];
        }
        if (!$this->helperData->isShippingDetailsEnabled()) {
            return [];
        }

        $data         = [];
        $shippingRate = $this->getShippingRateData($product);
        if ($shippingRate) {
            $data['shippingRate'] = $shippingRate;
        }

        if ($this->helperData->getShippingCountry()) {
            $data['shippingDestination'] = [
                '@type'          => 'DefinedRegion',
                'addressCountry' => $this->helperData->getShippingCountry()
            ];
        }

        $deliveryTime = $this->getDeliveryTimeData();
        if (!empty($deliveryTime)) {
            $data['deliveryTime'] = $deliveryTime;
        }

        if (empty($data)) {
            return [];
        }

        $data ['@type'] = 'OfferShippingDetails';

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isFreeShippingAllowed(\Magento\Catalog\Model\Product $product): bool
    {
        if (!$this->helperData->isFreeShippingEnabled()) {
            return false;
        }

        $code = $this->helperData->getFreeShippingCode();

        if ($code && $product->getData($code)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getMerchantReturnPolicyData(\Magento\Catalog\Model\Product $product): array
    {
//        if (!$this->isMerchantReturnPolicyAllowed($product)) {
//            return [];
//        }

        $data    = ['@type' => 'MerchantReturnPolicy'];
        $country = $this->helperData->getMerchantReturnPolicyApplicableCountry();
        if ($country) {
            $data['applicableCountry'] = $country;
        }

        $category = $this->helperData->getMerchantReturnPolicyCategory();
        if ($category) {
            if ($category === \MageWorx\SeoMarkup\Model\Source\MerchantReturnPolicy\Categories::FINITE_RETURN_WINDOW) {
                if ($this->helperData->getMerchantReturnPolicyDays()) {
                    $data['returnPolicyCategory'] = self::SCHEMA_ORG_URL . $category;
                    $data['merchantReturnDays']   = $this->helperData->getMerchantReturnPolicyDays();
                }
            } else {
                $data['returnPolicyCategory'] = self::SCHEMA_ORG_URL . $category;
            }
        }

        if ($this->helperData->getMerchantReturnPolicyMethod()) {
            $data['returnMethod'] = self::SCHEMA_ORG_URL . $this->helperData->getMerchantReturnPolicyMethod();
        }

        $fees = $this->helperData->getMerchantReturnPolicyFees();
        // @see https://developers.google.com/search/docs/appearance/structured-data/product#merchant-return-policy-properties
        if (in_array(
            $fees,
            [MerchantReturnPolicyFees::FREE_RETURN, MerchantReturnPolicyFees::RETURN_FEES_CUSTOMER_RESPONSIBILITY]
        )) {
            $data['returnFees'] = self::SCHEMA_ORG_URL . $fees;
        } elseif ($this->helperData->getShippingFeesAmount()) {
            if ($fees === MerchantReturnPolicyFees::RETURN_SHIPPING_FEES) {
                $data['returnFees'] = self::SCHEMA_ORG_URL . $fees;
            }

            $data['returnShippingFeesAmount'] = [
                '@type'    => 'MonetaryAmount',
                'currency' => $this->getCurrentCurrencyCode(),
                'value'    => $this->helperData->getShippingFeesAmount()
            ];
        }
//    var_dump($data);die;
        return $data;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getConditionValue($product)
    {
        if (!is_null($this->conditionValue)) {
            return $this->conditionValue;
        }

        if (!$this->helperData->isConditionEnabled()) {
            return $this->conditionValue = '';
        }

        $attributeCode      = $this->helperData->getConditionCode();
        $conditionByDefault = $this->helperData->getConditionDefaultValue();

        if ($attributeCode) {
            $conditionValue = $this->getAttributeValueByCode($product, $attributeCode);

            switch ($conditionValue) {
                case $this->helperData->getConditionValueForNew():
                    $conditionValue = "NewCondition";
                    break;
                case $this->helperData->getConditionValueForUsed():
                    $conditionValue = "UsedCondition";
                    break;
                case $this->helperData->getConditionValueForRefurbished():
                    $conditionValue = "RefurbishedCondition";
                    break;
                case $this->helperData->getConditionValueForDamaged():
                    $conditionValue = "DamagedCondition";
                    break;
                default:
                    if ($conditionByDefault) {
                        $conditionValue = $conditionByDefault;
                    }
                    break;
            }
        } elseif ($conditionByDefault) {
            $conditionValue = $conditionByDefault;
        }

        $conditionValue       = !empty($conditionValue) ? $conditionValue : false;
        $this->conditionValue = $conditionValue;

        return $this->conditionValue;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $useMagentoBestRating
     * @return array
     */
    public function getAggregateRatingData($product, $useMagentoBestRating = true)
    {
        if (!is_null($this->ratingData)) {
            return $this->ratingData;
        }

        $reviewDataObject = $this->getReviewDataObject($product);

        if (!is_object($reviewDataObject) || (is_object($reviewDataObject) && !$reviewDataObject->getData())) {
            $this->ratingData = [];

            return $this->ratingData;
        }

        $reviewData = $reviewDataObject->getData();

        if (empty($reviewData['reviews_count'])) {
            $this->ratingData = [];

            return $this->ratingData;
        }

        $reviewCount  = $reviewData['reviews_count'];
        $reviewRating = $reviewData['rating_summary'];

        $data = [];

        if ($this->helperData->getBestRating() && !$useMagentoBestRating) {
            $bestRating = $this->helperData->getBestRating();
            $rating     = round(($reviewRating / (100 / $bestRating)), 1);
        } else {
            $bestRating = 100;
            $rating     = $reviewRating;
        }

        $data['ratingValue'] = $rating;
        $data['reviewCount'] = $reviewCount;
        $data['bestRating']  = $bestRating;
        $data['worstRating'] = 0;

        $this->ratingData = $data;

        return $this->ratingData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $useMagentoBestRating
     * @return array
     */
    public function getReviewData($product, $useMagentoBestRating = true)
    {
        //Reviews are loaded using AJAX (magento 2.3.2), we can't use loaded collection from the block

        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $reviewCollection */
        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setDateOrder();

        $review = [];
        $data   = [];

        foreach ($reviewCollection->getData() as $datum) {

            $review['@type']         = 'Review';
            $review['name']          = $datum['title'];
            $review['description']   = $datum['detail'];
            $review['datePublished'] = $datum['created_at'];
            $review['author']        = $this->getAuthorData($datum['nickname']);

            $reviewRatingsData = $this->getReviewRatingsData($datum['review_id'], $useMagentoBestRating);

            if ($reviewRatingsData) {
                $review['reviewRating'] = $reviewRatingsData;
            }

            $data[] = $review;
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array|null
     */
    protected function getShippingRateData(\Magento\Catalog\Model\Product $product): ?array
    {
        $data = [
            '@type'    => 'MonetaryAmount',
            'currency' => $this->getCurrentCurrencyCode()
        ];

        if ($this->isFreeShippingAllowed($product)) {
            $data['value'] = 0;
        } elseif ($this->helperData->getShippingCost() !== null) {
            $data['value'] = $this->helperData->getShippingCost();
        } elseif ($this->helperData->getMaxShippingCost() !== null) {
            $data['maxValue'] = $this->helperData->getMaxShippingCost();
        } else {
            return null;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getDeliveryTimeData(): array
    {
        $data = [];

        if (!empty($this->helperData->getBusinessDaysForShippingDetails())) {
            $data['businessDays'] = [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => $this->helperData->getBusinessDaysForShippingDetails()
            ];
        }

        if ($this->helperData->getCutoffTimeForShippingDetails()) {
            $data['cutoffTime'] = $this->helperData->getCutoffTimeForShippingDetails();
        }

        if ($this->helperData->getMinDaysForHandlingTime() !== null
            && $this->helperData->getMaxDaysForHandlingTime() !== null
        ) {
            $data['handlingTime'] = [
                '@type'    => 'QuantitativeValue',
                'minValue' => $this->helperData->getMinDaysForHandlingTime(),
                'maxValue' => $this->helperData->getMaxDaysForHandlingTime(),
                'unitCode' => 'DAY'
            ];
        }

        if ($this->helperData->getMinDaysForTransitTime() !== null
            && $this->helperData->getMaxDaysForTransitTime() !== null
        ) {
            $data['transitTime'] = [
                '@type'    => 'QuantitativeValue',
                'minValue' => $this->helperData->getMinDaysForTransitTime(),
                'maxValue' => $this->helperData->getMaxDaysForTransitTime(),
                'unitCode' => 'DAY'
            ];
        }

        if (empty($data)) {
            return [];
        }

        $data['@type'] = 'ShippingDeliveryTime';

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function isMerchantReturnPolicyAllowed(\Magento\Catalog\Model\Product $product): bool
    {
        if (!$this->helperData->isMerchantReturnPolicyEnabled()) {
            return false;
        }

        if ($product->isVirtual()) {
            return false;
        }

        $code = $this->helperData->getMerchantReturnPolicyCode();

        if ($code && $product->getData($code)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $nickname
     * @return array
     */
    protected function getAuthorData($nickname)
    {
        $data          = [];
        $data['@type'] = 'Person';
        $data['name']  = $nickname;

        return $data;
    }

    /**
     * @param int $reviewId
     * @param bool $useMagentoBestRating
     * @return array
     */
    protected function getReviewRatingsData($reviewId, $useMagentoBestRating)
    {
        $collection = $this->ratingVoteCollectionFactory->create();

        $collection
            ->addFieldToFilter('review_id', $reviewId)
            ->addOrder('rating_id', 'ASC');

        $collectionData = $collection->getData();

        if (empty($collectionData)) {
            return [];
        }

        $count   = count($collectionData);
        $percent = 0;

        foreach ($collectionData as $ratingDatum) {
            $percent += $ratingDatum['percent'];
        }

        $percent = $percent / $count;

        if ($this->helperData->getBestRating() && !$useMagentoBestRating) {
            $bestRating  = $this->helperData->getBestRating();
            $ratingValue = round(($percent / (100 / $bestRating)), 1);
        } else {
            $bestRating  = 100;
            $ratingValue = $percent;
        }

        $data                = [];
        $data['@type']       = 'Rating';
        $data['worstRating'] = 0;
        $data['bestRating']  = $bestRating;
        $data['ratingValue'] = $ratingValue;

        return $data;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getColorValue($product)
    {
        if ($this->helperData->isColorEnabled()) {
            $attributeCode = $this->helperData->getColorCode();
            if ($attributeCode) {
                return $this->getAttributeValueByCode($product, $attributeCode);
            }
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getBrandValue($product)
    {
        if ($this->helperData->isBrandEnabled()) {
            $attributeCode = $this->helperData->getBrandCode();
            if ($attributeCode) {
                return $this->getAttributeValueByCode($product, $attributeCode);
            }
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getManufacturerValue($product)
    {
        if ($this->helperData->isManufacturerEnabled()) {
            $attributeCode = $this->helperData->getManufacturerCode();
            if ($attributeCode) {
                return $this->getAttributeValueByCode($product, $attributeCode);
            }
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getModelValue($product)
    {
        if ($this->helperData->isModelEnabled()) {
            $attributeCode = $this->helperData->getModelCode();
            if ($attributeCode) {
                return $this->getAttributeValueByCode($product, $attributeCode);
            }
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return ?array
     */
    public function getGtinData($product)
    {
        if ($this->helperData->isGtinEnabled()) {
            $attributeCode = $this->helperData->getGtinCode();
            if (!$attributeCode) {
                return null;
            }

            $gtinValue = $this->getAttributeValueByCode($product, $attributeCode);
            if (preg_match('/^[0-9]+$/', $gtinValue)) {
                if (strlen($gtinValue) == 8) {
                    $gtinType = 'gtin8';
                } elseif (strlen($gtinValue) == 12) {
                    $gtinValue = '0' . $gtinValue;
                    $gtinType  = 'gtin13';
                } elseif (strlen($gtinValue) == 13) {
                    $gtinType = 'gtin13';
                } elseif (strlen($gtinValue) == 14) {
                    $gtinType = 'gtin14';
                }
            }
        }

        return !empty($gtinType) ? ['gtinType' => $gtinType, 'gtinValue' => $gtinValue] : null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSkuValue($product)
    {
        if ($this->helperData->isSkuEnabled()) {
            $attributeCode = $this->helperData->getSkuCode();
            if ($attributeCode) {
                $sku = $this->getAttributeValueByCode($product, $attributeCode);
            } else {
                $sku = $product->getSku();
            }

            return $sku;
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    public function getWeightValue($product)
    {
        if ($this->helperData->isWeightEnabled()) {
            $weightValue = $product->getWeight();

            if ($weightValue) {
                $weightUnit = $this->helperData->getWeightUnit();

                return $weightValue . ' ' . $weightUnit;
            }
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSameAsValue(\Magento\Framework\DataObject $product): array
    {
        if ($this->helperData->isSameAsEnabled()) {
            $sameAsValue = (string)$product->getData('meta_same_as');
            $sameAsValue = array_filter((array)preg_split('/\r?\n/', $sameAsValue));
            $sameAsValue = array_map('trim', $sameAsValue);
            $sameAsValue = array_filter($sameAsValue);

            if (!empty($sameAsValue)) {
                return $sameAsValue;
            }
        }

        return [];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return null|string
     */
    public function getPriceValidUntilValue($product)
    {
        if ($this->helperData->isUseSpecialPriceFunctionality()) {
            $storeTimeStamp = $this->timezone->scopeTimeStamp($product->getStore());
            $fromDate       = $product->getSpecialFromDate();

            if ($fromDate) {
                $fromTimeStamp = strtotime($fromDate);

                if (!$this->dateTime->isEmptyDate($fromDate) && $storeTimeStamp < $fromTimeStamp) {
                    return date(DateTime::DATE_PHP_FORMAT, $fromTimeStamp);
                }
            }

            $toDate = $product->getSpecialToDate();

            if ($toDate) {
                $toTimeStamp = strtotime($toDate);

                // fix date YYYY-MM-DD 00:00:00 to YYYY-MM-DD 23:59:59
                $toTimeStamp += 86399;

                if (!$this->dateTime->isEmptyDate($toDate) && $storeTimeStamp < $toTimeStamp) {
                    return date(DateTime::DATE_PHP_FORMAT, $toTimeStamp);
                }
            }
        }

        $value = $this->helperData->getPriceValidUntilDefaultValue();

        if ($value && strtotime($value)) {
            $value = date(DateTime::DATE_PHP_FORMAT, strtotime($value, 0));

            return $this->dateTime->isEmptyDate($value) ? null : $value;
        }

        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return null|string
     */
    public function getProductIdValue($product)
    {
        $attributeCode = $this->helperData->getProductIdCode();

        if ($attributeCode) {
            $attributeValue = $this->getAttributeValueByCode($product, $attributeCode);

            return is_array($attributeValue) ? null : $attributeValue;
        }

        return null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $propertyName
     * @return string
     */
    public function getCustomPropertyValue($product, $propertyName)
    {
        $customProperty = $this->getAttributeValueByCode($product, $propertyName);

        return $customProperty ? $customProperty : null;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCategoryValue($product)
    {
        if (!$this->helperData->isCategoryEnabled()) {
            return null;
        }

        if (!is_null($this->categoryName)) {
            return $this->categoryName;
        }

        $categories         = $product->getCategoryCollection()->exportToArray();
        $currentCategory    = $this->registry->registry('current_category');
        $useDeepestCategory = $this->helperData->isCategoryDeepest();

        if (is_object($currentCategory)) {
            if (!count($categories)) {
                $this->categoryName = $currentCategory->getName();

                return $this->categoryName;
            }

            if ($useDeepestCategory) {
                $currentId    = $currentCategory->getId();
                $currentLevel = $currentCategory->getLevel();
                if (!is_numeric($currentLevel)) {
                    $this->categoryName = $currentCategory->getName();

                    return $this->categoryName;
                }

                foreach ($categories as $category) {
                    if ($category['level'] > $currentLevel) {
                        $currentId    = $category['entity_id'];
                        $currentLevel = $category['level'];
                    }
                }
                if ($currentId != $currentCategory->getId()) {
                    $categoryName = $this->getCategoryNameById($currentId);
                }
            }
            if (empty($categoryName)) {
                $this->categoryName = $currentCategory->getName();
            }
        } else {
            if (!$useDeepestCategory || !count($categories)) {
                $this->categoryName = '';

                return $this->categoryName;
            }

            $currentId    = 0;
            $currentLevel = 0;
            if (is_numeric($currentLevel)) {
                foreach ($categories as $category) {
                    if ($category['level'] >= $currentLevel) {
                        $currentId    = $category['entity_id'];
                        $currentLevel = $category['level'];
                    }
                }
                if ($currentId) {
                    $this->categoryName = $this->getCategoryNameById($currentId);
                }
            }
        }

        return $this->categoryName;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getProductImage($product, $imageId = 'product_base_image')
    {
        return $this->imageBuilder->setProduct($product)
                                  ->setImageId($imageId)
                                  ->setAttributes([])
                                  ->create();
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function getAvailability($product)
    {
        return $product->isAvailable();
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param ProductInterface $product
     * @param bool $extendInformation
     * @return Image[]
     */
    public function getProductImages(ProductInterface $product, bool $extendInformation = false): array
    {
        $images = $this->getMediaGalleryImages($product);
        if ($images instanceof \Magento\Framework\Data\Collection) {
            /** @var $image Image */
            foreach ($images as $image) {

                $imageId = (int)($image->getData('entity_id') ?? 0);
                if($extendInformation && $imageId !== 0) {
                    try {
                        $fileInfo = $this->getById->execute($imageId);
                    } catch (IntegrationException $e) {
                    } catch (NoSuchEntityException $e) {
                        $fileInfo = null;
                    }
                }

                $imageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), $this->getImageDisplayAria());
                $image->setData('image_url', $imageUrl);

                if(!empty($fileInfo)) {
                    $image->setData('width', $fileInfo->getWidth() ?? 0);
                    $image->setData('height', $fileInfo->getHeight() ?? 0);
                }
            }
            $images = $images->getItems();
        }

        return $images;
    }

    /**
     * @param ProductInterface $product
     * @return string[]
     */
    public function getProductImagesArray(ProductInterface $product): array
    {
        $urls = [];
        $images = $this->getProductImages($product);

        foreach ($images as $image) {
            $urls[] = $image->getData('image_url');
        }

        return array_filter($urls);
    }

    /**
     * Return one of the product_page_image_large, product_page_image_medium or product_page_image_small
     * vendor/magento/module-catalog/etc/frontend/di.xml:89
     * vendor/magento/theme-frontend-blank/etc/view.xml:75
     *
     * @return string
     */
    protected function getImageDisplayAria():string
    {
        return self::IMAGE_DISPLAY_AREA;
    }

    protected function getMediaGalleryImages(ProductInterface $product) : \Magento\Framework\Data\Collection
    {
        if(empty($product->getMediaGalleryImages()) || empty($product->getMediaGalleryImages()->getItems())) {
            $this->galleryReadHandler->execute($product);
        }
        return $product->getMediaGalleryImages();
    }

    /**
     *
     * @param int $id
     * @return string
     */
    protected function getCategoryNameById($id)
    {
        if ($id) {
            $storeId = $this->storeManager->getStore()->getId();

            return $this->resourceCategory->getAttributeRawValue(
                $id,
                'name',
                $this->storeManager->getStore($storeId)
            );
        }

        return '';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getReviewDataObject($product)
    {
        if ($this->helperVersion->checkModuleVersion('Magento_Review', '100.3.3')) {
            // Magento >= 2.3.3 retrieve string from getRatingSummary()

            if ($product->getRatingSummary() === null) {

                // Don't replace to Magento\Review\Model\ReviewSummaryFactory::class while module is compatible
                // with 2.3.0-2.3.2 - for avoid magento marketplace issue detector.
                $reviewSummaryFactory = ObjectManager::getInstance()->get('\Magento\Review\Model\ReviewSummaryFactory');

                /** @var \Magento\Review\Model\ReviewSummary $reviewSummary */
                $reviewSummary = $reviewSummaryFactory->create();

                $reviewSummary->appendSummaryDataToObject($product, $this->storeManager->getStore()->getId());
            }

            $reviewDataObject = new \Magento\Framework\DataObject();

            if ($product->getRatingSummary()) {
                $reviewDataObject->setData('reviews_count', $product->getData('reviews_count'));
                $reviewDataObject->setData('rating_summary', $product->getData('rating_summary'));
            }
        } else {
            //  Magento < 2.3.3 retrieve object from getRatingSummary()
            if (!$product->getRatingSummary()) {
                $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
            }

            $reviewDataObject = $product->getRatingSummary();
        }

        return $reviewDataObject;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->attributeValues     = [];
        $this->productCanonicalUrl = null;
        $this->conditionValue      = null;
        $this->ratingData          = null;
    }
}
