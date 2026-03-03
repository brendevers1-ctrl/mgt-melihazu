<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup;

use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;
use MageWorx\SeoMarkup\Model\TwitterCardsConfigProvider;

abstract class Page extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $pageModel;

    /**
     * Page constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     * @param TwitterCardsConfigProvider $twCardsConfigProvider
     * @param \Magento\Cms\Model\Page $pageModel
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        OpenGraphConfigProvider $openGraphConfigProvider,
        TwitterCardsConfigProvider $twCardsConfigProvider,
        \Magento\Cms\Model\Page $pageModel,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->pageModel = $pageModel;
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
     *
     * @return string
     */
    abstract protected function getTwImageUrl();

    /**
     *
     * @return boolean
     */
    abstract protected function isOgEnabled();

    /**
     *
     * @return boolean
     */
    abstract protected function isTwEnabled();

    /**
     *
     * @return string
     */
    abstract protected function getTwUsername();

    /**
     *
     * @return string
     */
    abstract protected function getOgType();

    /**
     *
     * @return string
     */
    abstract protected function getTwType();

    /**
     * @return string
     */
    public function getPreparedUrl(): string
    {
        list($urlRaw) = explode('?', $this->_urlBuilder->getCurrentUrl());

        return rtrim($urlRaw, '/');
    }

    /**
     *
     * @return string
     */
    protected function getMarkupHtml(): string
    {
        $html = '';

        if (!$this->isOgEnabled() && !$this->isTwEnabled()) {
            return $html;
        }

        if ($this->isOgEnabled()) {
            $html .= $this->getOpenGraphPageInfo();
        }

        if ($this->isTwEnabled()) {
            $html .= $this->getTwitterPageInfo();
        }

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getOpenGraphPageInfo(): string
    {
        $imageData = $this->getOgImageData();
        $siteName  = $this->escapeHtml($this->helperWebsite->getName());

        $html = "\n<meta property=\"og:type\" content=\"" . $this->getOgType() . "\"/>\n";
        $html .= "<meta property=\"og:title\" content=\"" . $this->getTitleForOpenGraph() . "\"/>\n";
        $html .= "<meta property=\"og:description\" content=\"" . $this->getDescriptionForOpenGraph() . "\"/>\n";
        $html .= "<meta property=\"og:url\" content=\"" . $this->getPreparedUrl() . "\"/>\n";
        if ($siteName) {
            $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
        }

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

    /**
     *
     * @return string
     */
    protected function getTwitterPageInfo(): string
    {
        $twitterUsername = $this->getTwUsername();
        $imageUrl        = '';

        $html = "<meta name=\"twitter:card\" content=\"" . $this->getTwType() . "\"/>\n";
        $html .= "<meta name=\"twitter:site\" content=\"" . $twitterUsername . "\"/>\n";
        $html .= "<meta name=\"twitter:title\" content=\"" . $this->getTitleForTwitterCards() . "\"/>\n";
        $html .= "<meta name=\"twitter:description\" content=\"" . $this->getDescriptionForTwitterCards() . "\"/>\n";

        if ($imageUrl) {
            $html .= "<meta name=\"twitter:image\" content=\"" . $imageUrl . "\"/>\n";
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function getTitleForOpenGraph(): string
    {
        $code  = $this->openGraphConfigProvider->getPageTitleCode();
        $title = '';

        if ($code && $this->getEntity()) {
            $title = strip_tags((string)$this->getEntity()->getData($code));
        }

        if (!$title) {
            $title = $this->pageConfig->getTitle()->get();

            if (!$title && $this->getEntity()) {
                $title = $this->getEntity()->getMetaTitle() ?: $this->getEntity()->getTitle();
            }
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @return string
     */
    protected function getDescriptionForOpenGraph(): string
    {
        $code        = $this->openGraphConfigProvider->getPageDescriptionCode();
        $description = '';

        if ($code && $this->getEntity()) {
            $description = $this->getEntity()->getData($code);
        }

        if (!$description) {
            $description = $this->pageConfig->getDescription();

            if (!$description && $this->getEntity()) {
                $description = $this->getEntity()->getMetaDescription();
            }
        }

        return $description ? $this->escapeHtmlAttr(strip_tags((string)$description)) : '';
    }

    /**
     * @return string
     */
    protected function getTitleForTwitterCards(): string
    {
        $code  = $this->twCardsConfigProvider->getPageTitleCode();
        $title = '';

        if ($code && $this->getEntity()) {
            $title = strip_tags((string)$this->getEntity()->getData($code));
        }

        if (!$title) {
            $title = $this->pageConfig->getTitle()->get();

            if (!$title && $this->getEntity()) {
                $title = $this->getEntity()->getMetaTitle() ?: $this->getEntity()->getTitle();
            }
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @return string
     */
    protected function getDescriptionForTwitterCards(): string
    {
        $code        = $this->twCardsConfigProvider->getPageDescriptionCode();
        $description = '';

        if ($code && $this->getEntity()) {
            $description = $this->getEntity()->getData($code);
        }

        if (!$description) {
            $description = $this->pageConfig->getDescription();

            if (!$description && $this->getEntity()) {
                $description = $this->getEntity()->getMetaDescription();
            }
        }

        return $description ? $this->escapeHtmlAttr(strip_tags((string)$description)) : '';
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getEntity()
    {
        if ($this->entity) {
            return $this->entity;
        }

        if ($this->pageModel->getId()) {
            return $this->pageModel;
        }

        return null;
    }
}
