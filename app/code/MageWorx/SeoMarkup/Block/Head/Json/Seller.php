<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Head\Json;

use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

class Seller extends \MageWorx\SeoMarkup\Block\Head\Json
{
    /**
     *
     * @var \MageWorx\SeoMarkup\Helper\Seller
     */
    protected $helperSeller;

    /**
     *
     * @param \MageWorx\SeoMarkup\Helper\Seller $helperSeller
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\Seller $helperSeller,
        \Magento\Framework\View\Element\Template\Context $context,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider,
        array $data = []
    ) {
        $this->helperSeller = $helperSeller;
        parent::__construct($context, $data, $seoFeaturesStatusProvider);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html = '';

        if (!$this->helperSeller->isRsEnabled()) {
            return $html;
        }

        if ($this->helperSeller->isShowForAllPages()
            || ($this->helperSeller->isShowOnlyForHomePage() && $this->isHomePage())
        ) {
            $sellerJsonData = $this->getJsonOrganizationData();
            $sellerJson     = empty($sellerJsonData) ? '' : json_encode($sellerJsonData);

            if ($sellerJsonData) {
                $html .= '<script type="application/ld+json">' . $sellerJson . '</script>';
            }
        }

        return $html;
    }

    /**
     * @return bool
     */
    protected function isHomePage(): bool
    {
        if ($this->getData('is_home_page')) {
            return true;
        }

        $fullActionName = $this->getRequest()->getFullActionName();

        return in_array($fullActionName, ['cms_index_index', 'cms_index_defaultIndex']);
    }

    /**
     * @return array
     */
    protected function getJsonOrganizationData(): array
    {
        $name  = $this->helperSeller->getName();
        $image = $this->getImageUrl();

        if (!$name || !$image) { // Name and Image are required fields
            return [];
        }
        $data             = [];
        $data['@context'] = 'http://schema.org';
        $data['@type']    = $this->helperSeller->getType();
        $data['@id']      = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $name             = $this->helperSeller->getName();
        if ($name) {
            $data['name'] = $name;
        }

        $description = $this->helperSeller->getDescription();
        if ($description) {
            $data['description'] = $description;
        }

        $openingHours = $this->helperSeller->getOpeningHours();
        if (!empty($openingHours)) {
            $data['openingHours'] = $openingHours;
        }

        $phone = $this->helperSeller->getPhone();
        if ($phone) {
            $data['telephone'] = $phone;
        }

        $email = $this->helperSeller->getEmail();
        if ($email) {
            $data['email'] = $email;
        }

        $fax = $this->helperSeller->getFax();
        if ($fax) {
            $data['faxNumber'] = $fax;
        }

        $address = $this->getAddress();
        if ($address && count($address) > 1) {
            $data['address'] = $address;
        }

        $socialLinks = $this->helperSeller->getSameAsLinks();
        if (is_array($socialLinks) && !empty($socialLinks)) {
            $data['sameAs']   = [];
            $data['sameAs'][] = $socialLinks;
        }

        if ($image) {
            $data['image'] = $image;
        }

        $priceRange = $this->helperSeller->getPriceRange();
        if ($priceRange) {
            $data['priceRange'] = $priceRange;
        }

        $data['url'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        return $data;
    }

    /**
     * @return array
     */
    protected function getAddress(): array
    {
        return [
            '@type'           => 'PostalAddress',
            'addressCountry'  => $this->helperSeller->getCountryCode(),
            'addressLocality' => $this->helperSeller->getLocation(),
            'addressRegion'   => $this->helperSeller->getRegionAddress(),
            'streetAddress'   => $this->helperSeller->getStreetAddress(),
            'postalCode'      => $this->helperSeller->getPostCode()
        ];
    }

    /**
     *
     * @return string|boolean
     */
    protected function getImageUrl()
    {
        $folderName  = 'seller_image';
        $storeConfig = $this->helperSeller->getImage();
        $faviconFile = $this->_storeManager->getStore()->getBaseUrl('media') . $folderName . '/' . $storeConfig;
        if (!is_null($storeConfig)) {
            return $faviconFile;
        }

        return false;
    }
}
