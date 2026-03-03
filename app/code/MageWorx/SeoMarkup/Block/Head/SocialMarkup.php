<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;
use MageWorx\SeoMarkup\Model\TwitterCardsConfigProvider;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

abstract class SocialMarkup extends \MageWorx\SeoMarkup\Block\Head
{
    /**
     * @var \MageWorx\SeoMarkup\Helper\Website
     */
    protected $helperWebsite;

    /**
     * @var OpenGraphConfigProvider
     */
    protected $openGraphConfigProvider;

    /**
     * @var TwitterCardsConfigProvider
     */
    protected $twCardsConfigProvider;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoMarkup';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * SocialMarkup constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     * @param TwitterCardsConfigProvider $twCardsConfigProvider
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        OpenGraphConfigProvider $openGraphConfigProvider,
        TwitterCardsConfigProvider $twCardsConfigProvider,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider

    ) {
        $this->registry                  = $registry;
        $this->helperWebsite             = $helperWebsite;
        $this->openGraphConfigProvider   = $openGraphConfigProvider;
        $this->twCardsConfigProvider     = $twCardsConfigProvider;
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
            return '';
        }

        return $this->getMarkupHtml();
    }

    /**
     * Retrieve facebook logo
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOgImageData()
    {
        $imageData   = [];
        $folderName  = 'og_image';
        $storeConfig = $this->helperWebsite->getOgImage();
        $filePath    = $folderName . DIRECTORY_SEPARATOR . $storeConfig;
        $imageUrl    = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filePath;

        if ($storeConfig !== '') {
            $imageData['url'] = $imageUrl;

            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

            if ($mediaDir->isFile($filePath)) {
                $absolutePath = $mediaDir->getAbsolutePath($filePath);
                $imageAttr    = getimagesize($absolutePath);

                $imageData['width']  = $imageAttr[0];
                $imageData['height'] = $imageAttr[1];
            }
        }

        return $imageData;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function renderUrl($url)
    {
        if (in_array(parse_url($this->_urlBuilder->getCurrentUrl(), PHP_URL_PATH), ['/graphql', '/graphql/'])) {
            $baseUrl = explode('?', $this->_urlBuilder->getBaseUrl())[0];

            return str_replace($baseUrl, '', $url);
        }

        return $url;
    }

    /**
     * @param string $description
     * @param string $size
     * @return string
     */
    protected function getCroppedDescription($description, $size)
    {
        if ($description) {
            $description = str_replace('&#x20;', ' ', $description);
            if (strlen($description) > $size) {
                $description = substr($description, 0, $size);
                if (str_contains($description, ' ')) {
                    $description = strrev($description);
                    $lastWord = strpos($description, ' ');
                    $description = strrev($description);
                    $description = substr($description, 0,$size - $lastWord - 1);
                }
            }
        }

        return $description;
    }
}
