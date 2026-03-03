<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Converter;

use MageWorx\SeoXTemplates\Model\Converter;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use MageWorx\SeoXTemplates\Helper\Converter as HelperConverter;
use MageWorx\SeoXTemplates\Model\LayeredFiltersProviderFactory;

abstract class LandingPage extends Converter
{
    /**
     * @var LayeredFiltersProviderFactory
     */
    protected $layeredFiltersProviderFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param HelperConverter $helperConverter
     * @param \MageWorx\SeoXTemplates\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Framework\App\Request\Http $request
     * @param LayeredFiltersProviderFactory $layeredFilterProviderFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HelperData $helperData,
        HelperConverter $helperConverter,
        \MageWorx\SeoXTemplates\Model\ResourceModel\Category $resourceCategory,
        \Magento\Framework\App\Request\Http $request,
        LayeredFiltersProviderFactory $layeredFilterProviderFactory
    ) {
        parent::__construct($storeManager, $helperData, $helperConverter, $resourceCategory, $request);
        $this->layeredFiltersProviderFactory = $layeredFilterProviderFactory;
    }

    /**
     * Retrieve converted string by template code
     *
     * @param array $vars
     * @param string $templateCode
     * @return string
     */
    protected function __convert($vars, $templateCode)
    {
        $convertValue = $templateCode;
        foreach ($vars as $key => $params) {
            if (!$this->isDynamically && $this->_issetDynamicAttribute($params['attributes'])) {
                $value = $key;
            } else {
                foreach ($params['attributes'] as $attributeCode) {
                    switch ($attributeCode) {
                        case 'landing_page':
                            $value = $this->_convertName();
                            break;
                        case 'meta_title':
                        case 'meta_description':
                        case 'meta_keywords':
                        case 'header':
                        case 'text_1':
                        case 'text_2':
                        case 'text_3':
                        case 'text_4':
                            $value = $this->_convertData($attributeCode);
                            break;
                        case 'store_view_name':
                            $value = $this->_convertStoreViewName();
                            break;
                        case 'store_name':
                            $value = $this->_convertStoreName();
                            break;
                        case 'website_name':
                            $value = $this->_convertWebsiteName();
                            break;
                        default:
                            if (strpos($attributeCode, 'filter_') === 0) {
                                $value = $this->_convertFilter($attributeCode);
                            }
                            break;
                    }

                    if ($value) {
                        $prefix = $this->helperConverter->randomizePrefix($params['prefix']);
                        $suffix = $this->helperConverter->randomizeSuffix($params['suffix']);
                        $value  = $prefix . $value . $suffix;
                        break;
                    }
                }
            }
            $convertValue = str_replace($key, (string)$value, $convertValue);
        }

        return $this->_render($convertValue);
    }

    /**
     *
     * @return string
     */
    protected function _convertName()
    {
        return $this->item->getTitle();
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    protected function _convertData($attribute)
    {
        return $this->item->getStoreValue($attribute, $this->item->getStoreId());
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreViewName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getGroup()->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertWebsiteName()
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getWebsite()->getName();
    }

    /**
     *
     * @param string $convertValue
     * @return string
     */
    protected function _render($convertValue)
    {
        return trim($convertValue);
    }

    /**
     * @param string $templateCode
     * @return bool
     */
    protected function stopProcess($templateCode)
    {
        if (!$this->isDynamically) {
            return false;
        }

        $isNotFound = true;

        if ($this->_issetDynamicAttribute([$templateCode], false)) {
            $isNotFound = false;
        }

        return $isNotFound;
    }

    /**
     * @param array $attributes
     * @param boolean $isStrict
     * @return bool
     */
    protected function _issetDynamicAttribute($attributes, $isStrict = true)
    {
        foreach ($attributes as $attribute) {
            if ($isStrict) {
                if (strpos(trim($attribute), 'filter_') === 0) {
                    return true;
                }
            } else {
                if (strpos(trim($attribute), 'filter_') !== 0) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function _convertFilter($attributeCode)
    {
        $attributeCode = str_replace('filter_', '', $attributeCode);

        if (!$attributeCode) {
            return '';
        }

        $value        = '';
        $commonFilter = [];

        $currentFiltersData = $this->getCurrentLayeredFilters();

        if (is_array($currentFiltersData) && count($currentFiltersData) > 0) {
            foreach ($currentFiltersData as $filter) {
                if ($attributeCode == 'all' || $attributeCode == $filter['code']) {
                    $commonFilter[$filter['name']][] = $filter['label'];
                } elseif ($attributeCode == 'all_value' || $attributeCode == $filter['code'] . '_value') {
                    $value .= strip_tags($filter['label']);
                } elseif ($attributeCode == 'all_label' || $attributeCode == $filter['code'] . '_label') {
                    $value .= $filter['name'];
                }
            }
        }

        foreach ($commonFilter as $filterName => $filterLabels) {
            $value .= $filterName . ": " . strip_tags(implode(', ', $filterLabels)) . '; ';
        }

        return rtrim($value, '; ');
    }

    /**
     * @return array
     */
    protected function getCurrentLayeredFilters(): array
    {
        $layeredFilterProvider = $this->layeredFiltersProviderFactory->create();

        return $layeredFilterProvider->getCurrentLayeredFilters();
    }
}
