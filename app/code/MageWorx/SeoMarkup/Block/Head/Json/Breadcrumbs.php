<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Block\Head\Json;

use Magento\Framework\UrlInterface;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

abstract class Breadcrumbs extends \MageWorx\SeoMarkup\Block\Head\Json
{
    /**
     *
     * @var \MageWorx\SeoMarkup\Helper\Breadcrumbs
     */
    protected $helperBreadcrumbs;

    /**
     *
     * @var string
     */
    protected $breadcrumbsBlockName = 'breadcrumbs';

    /**
     * @return array
     */
    abstract protected function getBreadcrumbs();

    /**
     * Breadcrumbs constructor.
     *
     * @param \MageWorx\SeoMarkup\Helper\Breadcrumbs $helperBreadcrumbs
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        \MageWorx\SeoMarkup\Helper\Breadcrumbs $helperBreadcrumbs,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        ?SeoFeaturesStatusProvider $seoFeaturesStatusProvider = null

    ) {
        $this->helperBreadcrumbs = $helperBreadcrumbs;
        parent::__construct($context, $data, $seoFeaturesStatusProvider);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html = '';

        if ($this->seoFeaturesStatusProvider) {
            if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
                return $html;
            }
        }

        if (!$this->helperBreadcrumbs->isRsEnabled()) {
            return $html;
        }

        $breadcrumbsJsonData = $this->getJsonBreadcrumbsData();
        $breadcrumbsJson     = $breadcrumbsJsonData ? json_encode($breadcrumbsJsonData) : '';

        if ($breadcrumbsJsonData) {
            $html .= '<script type="application/ld+json">' . $breadcrumbsJson . '</script>';
        }

        return $html;
    }

    /**
     *
     * @return array
     */
    protected function getJsonBreadcrumbsData(): array
    {
        $breadcrumbsBlock = $this->getBreadcrumbsBlock();
        if (!$breadcrumbsBlock) {
            return [];
        }

        $crumbsArray = $this->getBreadcrumbs();

        if (empty($crumbsArray)) {
            return [];
        }

        $crumbs    = array_values($crumbsArray);
        $listitems = [];

        $data             = [];
        $data['@context'] = 'http://schema.org';
        $data['@type']    = 'BreadcrumbList';

        for ($i = 1; $i < count($crumbs); $i++) {
            $listItem          = [];
            $listItem['@type'] = 'ListItem';

            if (!empty($crumbs[$i]['link'])) {
                $listItem['item']['@id'] = $crumbs[$i]['link'];
            } else {
                $currentUrl = $this->_urlBuilder->getCurrentUrl();

                $listItem['item']['@id'] = explode('?', $currentUrl)[0];
            }
            $listItem['item']['name'] = $crumbs[$i]['label'];
            $position                 = $i;
            $listItem['position']     = $position;

            $listitems[] = $listItem;
        }

        $data['itemListElement'] = $listitems;


        return !empty($data) ? $data : [];
    }

    /**
     *
     * @return \Magento\Theme\Block\Html\Breadcrumbs|null
     */
    protected function getBreadcrumbsBlock()
    {
        $block = $this->_layout->getBlock($this->breadcrumbsBlockName);

        if (!($block instanceof \Magento\Theme\Block\Html\Breadcrumbs)) {
            return null;
        }

        return $block;
    }

    /**
     * @param array $crumbs
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getHomeBreadcrumbs(array $crumbs = []): array
    {
        return $this->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_LINK)
            ],
            $crumbs
        );
    }

    /**
     *
     * @param string $crumbName
     * @param array $crumbInfo
     * @param array $crumbs
     * @param boolean $after
     * @return array
     */
    protected function addCrumb(string $crumbName, array $crumbInfo, array $crumbs, bool $after = false): array
    {
        $crumbInfo = $this->prepareArray($crumbInfo, ['label', 'title', 'link', 'first', 'last', 'readonly']);
        if ((!isset($crumbs[$crumbName])) || (!$crumbs[$crumbName]['readonly'])) {
            $crumbs[$crumbName] = $crumbInfo;
        }

        return $crumbs;
    }

    /**
     * Set required array elements
     *
     * @param array $arr
     * @param array $elements
     * @return  array
     */
    protected function prepareArray(array &$arr, array $elements = []): array
    {
        foreach ($elements as $element) {
            if (!isset($arr[$element])) {
                $arr[$element] = null;
            }
        }

        return $arr;
    }
}
