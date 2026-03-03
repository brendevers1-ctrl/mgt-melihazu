<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head;

use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

abstract class Json extends \MageWorx\SeoMarkup\Block\Head
{
    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoMarkup';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider = null

    ) {
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        if ($this->seoFeaturesStatusProvider) {
            if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
                return '';
            }
        }

        return $this->getMarkupHtml();
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
}
