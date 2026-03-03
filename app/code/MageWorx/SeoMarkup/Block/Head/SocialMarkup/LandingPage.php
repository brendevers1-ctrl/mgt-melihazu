<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup;

use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;
use MageWorx\SeoMarkup\Model\TwitterCardsConfigProvider;

class LandingPage extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup
{
    /**
     * @var \MageWorx\SeoMarkup\Helper\LandingPage
     */
    protected $helperLandingPage;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Website
     */
    protected $helperWebsite;

    /**
     * LandingPage constructor.
     *
     * @param \MageWorx\SeoMarkup\Helper\LandingPage $helperLandingPage
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     * @param TwitterCardsConfigProvider $twCardsConfigProvider
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\LandingPage $helperLandingPage,
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        OpenGraphConfigProvider $openGraphConfigProvider,
        TwitterCardsConfigProvider $twCardsConfigProvider,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->helperLandingPage = $helperLandingPage;
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
    protected function getMarkupHtml()
    {
        if (!$this->helperLandingPage->isOgEnabled()) {
            return '';
        }

        return $this->getSocialLandingPageInfo();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSocialLandingPageInfo()
    {
        $type        = 'product.group';
        $title       = $this->escapeHtml($this->pageConfig->getTitle()->get());
        $description = $this->escapeHtml($this->pageConfig->getDescription());
        $siteName    = $this->escapeHtml($this->helperWebsite->getName());

        list($urlRaw) = explode('?', $this->_urlBuilder->getCurrentUrl());
        $url = rtrim($urlRaw, '/');

        $html = "\n<meta property=\"og:type\" content=\"" . $type . "\"/>\n";
        $html .= "<meta property=\"og:title\" content=\"" . $title . "\"/>\n";
        $html .= "<meta property=\"og:description\" content=\"" . $description . "\"/>\n";
        $html .= "<meta property=\"og:url\" content=\"" . $url . "\"/>\n";
        if ($siteName) {
            $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
        }

        $imageData = $this->getOgImageData();

        if (isset($imageData['url'])) {
            $html .= "<meta property=\"og:image\" content=\"" . $imageData['url'] . "\"/>\n";

            if (isset($imageData['width'])) {
                $html .= "<meta property=\"og:image:width\" content=\"" . $imageData['width'] . "\"/>\n";
                $html .= "<meta property=\"og:image:height\" content=\"" . $imageData['height'] . "\"/>\n";
            }
        }

        if ($appId = $this->helperWebsite->getFacebookAppId()) {
            $html .= "<meta property=\"fb:app_id\" content=\"" . $appId . "\"/>\n";
        }

        return $html;
    }
}
