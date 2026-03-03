<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;
use MageWorx\SeoMarkup\Model\TwitterCardsConfigProvider;
use MageWorx\SeoMarkup\Helper\DataProvider\Category as HelperDataProvider;

class Category extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup
{
    /**
     * @var HelperDataProvider
     */
    protected $helperDataProvider;

    /**
     * Category constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     * @param TwitterCardsConfigProvider $twCardsConfigProvider
     * @param HelperDataProvider $helperDataProvider
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        OpenGraphConfigProvider $openGraphConfigProvider,
        TwitterCardsConfigProvider $twCardsConfigProvider,
        HelperDataProvider $helperDataProvider,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->helperDataProvider = $helperDataProvider;
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
    public function getPreparedUrl(): string
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();

        if (in_array(parse_url($currentUrl, PHP_URL_PATH), ['/graphql', '/graphql/']) && $this->getCategory()) {
            return $this->getCategory()->getUrl();
        }

        list($urlRaw) = explode('?', $currentUrl);

        return rtrim($urlRaw, '/');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getMarkupHtml(): string
    {
        if (!$this->openGraphConfigProvider->isEnabledForCategory() && !$this->isTwEnabled()) {
            return '';
        }

        return $this->getSocialCategoryInfo();
    }

    /**
     * @return bool
     */
    protected function isTwEnabled(): bool
    {
        return $this->twCardsConfigProvider->isEnabledForCategory() && $this->twCardsConfigProvider->getUsername();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSocialCategoryInfo(): string
    {
        $html     = '';
        $imageUrl = $this->getCategoryImageUrl();

        if ($this->openGraphConfigProvider->isEnabledForCategory()) {

            $type     = 'product.group';
            $siteName = $this->escapeHtml($this->helperWebsite->getName());

            $html = "\n<meta property=\"og:type\" content=\"" . $type . "\"/>\n";
            $html .= "<meta property=\"og:title\" content=\"" . $this->getTitleForOpenGraph() . "\"/>\n";
            $html .= "<meta property=\"og:description\" content=\"" . $this->getDescriptionForOpenGraph() . "\"/>\n";
            $html .= "<meta property=\"og:url\" content=\"" . $this->getPreparedUrl() . "\"/>\n";
            if ($siteName) {
                $html .= "<meta property=\"og:site_name\" content=\"" . $siteName . "\"/>\n";
            }

            if ($imageUrl) {
                $imageData = ['url' => $imageUrl];

                if ($this->getCategoryImageSize()) {
                    $imageData = array_merge($imageData, $this->getCategoryImageSize());
                }
            } else {
                $imageData = $this->getOgImageData();
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
        }

        if ($this->isTwEnabled()) {
            $html = $html ? $html : "\n";
            $html .= "<meta name=\"twitter:card\" content=\"summary\"/>\n";
            $html .= "<meta name=\"twitter:site\" content=\"" . $this->twCardsConfigProvider->getUsername() . "\"/>\n";
            $html .= "<meta name=\"twitter:title\" content=\"" . $this->getTitleForTwitterCards() . "\"/>\n";
            $html .= "<meta name=\"twitter:description\" content=\"" . $this->getDescriptionForTwitterCards()
                . "\"/>\n";

            if ($imageUrl) {
                $html .= "<meta name=\"twitter:image\" content=\"" . $imageUrl . "\"/>\n";
            }
        }

        return $html;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    protected function getCategoryImageUrl()
    {
        $category = $this->getCategory();
        $imageUrl = $category->getImageUrl();

        if (!$imageUrl || !is_string($imageUrl)) {
            return null;
        }

        $isRelativeUrl = substr($imageUrl, 0, 1) === '/';

        if ($isRelativeUrl) {
            $imageUrl = ltrim($imageUrl, '/');
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB) . $imageUrl;
        }

        return $imageUrl;
    }

    /**
     * @return array|bool
     */
    protected function getCategoryImageSize()
    {
        $category = $this->getCategory();
        $image    = $category->getData('image');

        if ($image && is_string($image)) {
            $mediaDir      = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $isRelativeUrl = substr($image, 0, 1) === '/';

            if ($isRelativeUrl) {
                if (strpos($image, '/' . DirectoryList::MEDIA . '/') === 0) {
                    $filePath = substr_replace($image, '', 0, strlen('/' . DirectoryList::MEDIA . '/'));
                } else {
                    return false;
                }
            } else {
                $filePath = 'catalog/category/' . $image;
            }

            if ($mediaDir->isFile($filePath)) {
                $absolutePath = $mediaDir->getAbsolutePath($filePath);
                $imageAttr    = getimagesize($absolutePath);

                return [
                    'width'  => $imageAttr[0],
                    'height' => $imageAttr[1]
                ];
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTitleForOpenGraph(): string
    {
        $code  = $this->openGraphConfigProvider->getCategoryTitleCode();
        $title = '';

        if ($code && $this->getCategory()) {
            $title = strip_tags($this->helperDataProvider->getAttributeValueByCode($this->getCategory(), $code));
        }

        if (!$title) {
            $title = $this->pageConfig->getTitle()->get();

            if (!$title && $this->getCategory()) {
                $title = $this->getCategory()->getMetaTitle() ?: $this->getCategory()->getName();
            }
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getDescriptionForOpenGraph(): string
    {
        $code        = $this->openGraphConfigProvider->getCategoryDescriptionCode();
        $description = '';

        if ($code && $this->getCategory()) {
            $description = strip_tags($this->helperDataProvider->getAttributeValueByCode($this->getCategory(), $code));
        }

        if (!$description) {
            $description = $this->pageConfig->getDescription();

            if (!$description && $this->getCategory()) {
                $description = $this->getCategory()->getMetaDescription() ?: $this->getCategory()->getDescription();
            }

        }

        $description = $description ? $this->escapeHtmlAttr(strip_tags($description)) : '';

        if ($this->openGraphConfigProvider->getCropCategoryDescription()) {
            $description = $this->getCroppedDescription(
                $description,
                $this->openGraphConfigProvider->getCropCategoryDescription()
            );
        }

        return $description;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTitleForTwitterCards(): string
    {
        $code  = $this->twCardsConfigProvider->getCategoryTitleCode();
        $title = '';

        if ($code && $this->getCategory()) {
            $title = strip_tags($this->helperDataProvider->getAttributeValueByCode($this->getCategory(), $code));
        }

        if (!$title) {
            $title = $this->pageConfig->getTitle()->get();

            if (!$title && $this->getCategory()) {
                $title = $this->getCategory()->getMetaTitle() ?: $this->getCategory()->getName();
            }
        }

        return $title ? $this->escapeHtmlAttr($title) : '';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getDescriptionForTwitterCards(): string
    {
        $code        = $this->twCardsConfigProvider->getCategoryDescriptionCode();
        $description = '';

        if ($code && $this->getCategory()) {
            $description = strip_tags($this->helperDataProvider->getAttributeValueByCode($this->getCategory(), $code));
        }

        if (!$description) {
            $description = $this->pageConfig->getDescription();

            if (!$description && $this->getCategory()) {
                $description = $this->getCategory()->getMetaDescription() ?: $this->getCategory()->getDescription();
            }
        }

        $description = $description ? $this->escapeHtmlAttr(strip_tags($description)) : '';

        if ($this->twCardsConfigProvider->getCropCategoryDescription()) {
            $description = $this->getCroppedDescription(
                $description,
                $this->twCardsConfigProvider->getCropCategoryDescription()
            );
        }

        return $description;
    }

    /**
     * @return \Magento\Catalog\Model\Category|null
     */
    protected function getCategory()
    {
        $category = $this->getEntity();

        if (!$category) {
            $category = $this->registry->registry('current_category');
        }

        return $category;
    }

}
