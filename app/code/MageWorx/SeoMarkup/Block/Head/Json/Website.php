<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Head\Json;

use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

class Website extends \MageWorx\SeoMarkup\Block\Head\Json
{

    /**
     *
     * @var \MageWorx\SeoMarkup\Helper\Website
     */
    protected $helperWebsite;

    /**
     *
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
	SeoFeaturesStatusProvider $seoFeaturesStatusProvider,
        array $data = []
    ) {
        $this->helperWebsite = $helperWebsite;
        parent::__construct($context, $data, $seoFeaturesStatusProvider);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html = '';

        if (!$this->helperWebsite->isRsEnabled()) {
            return $html;
        }

        $websiteJsonData = $this->getJsonWebSiteData();
        $websiteJson     = $websiteJsonData ? json_encode($websiteJsonData) : '';

        if ($websiteJsonData) {
            $html .= '<script type="application/ld+json">' . $websiteJson . '</script>';
        }

        return $html;
    }

    /**
     *
     * @return array
     */
    protected function getJsonWebSiteData(): array
    {
        $data             = [];
        $data['@context'] = 'http://schema.org';
        $data['@type']    = 'WebSite';
        $data['url']      = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $siteName = $this->helperWebsite->getName();
        if ($siteName) {
            $data['name'] = $siteName;
        }

        $siteAbout = $this->helperWebsite->getAboutInfo();
        if ($siteAbout) {
            $data['about'] = $siteAbout;
        }

        $potentialActionData = $this->getPotentialActionData();
        if ($potentialActionData) {
            $data['potentialAction'] = $potentialActionData;
        }

        return $data;
    }

    /**
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPotentialActionData()
    {
        if (!$this->isHomePage()) {
            return false;
        }

        if (!$this->helperWebsite->isAddWebsiteSearchAction()) {
            return false;
        }

        $storeBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $data                = array();
        $data['@type']       = 'SearchAction';
        $data['target']      = $storeBaseUrl . 'catalogsearch/result/?q={search_term_string}';
        $data['query-input'] = 'required name=search_term_string';

        return $data;
    }

    /**
     * @return bool
     */
    protected function isHomePage(): bool
    {
        if ($this->getData('is_home_page')) {
            return true;
        }

        return ($this->_request->getFullActionName() == 'cms_index_index');
    }
}
