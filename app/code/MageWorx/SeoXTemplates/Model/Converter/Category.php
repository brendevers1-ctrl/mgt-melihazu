<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Converter;

use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\SeoXTemplates\Model\Converter;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use MageWorx\SeoXTemplates\Helper\Converter as HelperConverter;
use MageWorx\SeoXTemplates\Model\LayeredFiltersProviderFactory;

abstract class Category extends Converter
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
                        case 'category':
                            $value = $this->_convertName();
                            break;
                        case 'price':
                        case 'special_price':
                            break;
                        case 'parent_category':
                            $value = $this->_convertParentCategory();
                            break;
                        case 'categories':
                            $value = $this->_convertCategories();
                            break;
                        case 'subcategories':
                            $value = $this->_convertSubCategories();
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
                            } elseif (strpos($attributeCode, 'parent_category_') === 0) {
                                $value = $this->_convertParentCategory($attributeCode);
                            } else {
                                $value = $this->_convertAttribute($attributeCode);
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
        return $this->item->getName();
    }

    /**
     *
     * @param string $attributeCode
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _convertParentCategory(string $attributeCode = ''): string
    {
        $level = str_replace('parent_category_', '', $attributeCode);
        $value = '';

        if (!$attributeCode || $level == '1') {
            $parentId = $this->item->getParentId();
            if ($parentId && !$this->isRootCategoryId($parentId)) {
                $value = $this->resourceCategory->getAttributeRawValue(
                    $parentId,
                    'name',
                    $this->storeManager->getStore($this->item->getStoreId())
                );
            }
            if ($value == 'Root Catalog') {
                $value = '';
            }
        } else {
            $categories = $this->_getParentCategoriesArray();
            if (!empty($categories) && is_array($categories) && count($categories) > 0) {
                $categories = array_reverse($categories);
                if (array_key_exists($level, $categories)) {
                    $value = $categories[$level];
                }
            }
        }

        return (string)$value;
    }

    /**
     * @return string
     */
    protected function _convertCategories()
    {
        $value     = '';
        $path      = $this->_getParentCategoriesArray();
        $separator = $this->helperData->getTitleSeparator($this->item->getStoreId());

        if (!empty($path) && is_array($path) && count($path) > 0) {
            $path  = array_filter($path);
            $value = join($separator, array_reverse($path));
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function _getParentCategoriesArray()
    {
        $paths = explode('/', $this->item->getPath());
        $paths = (is_array($paths)) ? array_slice($paths, 1) : $this->item->getParentCategories();
        $path  = [];

        if (is_array($paths)) {
            foreach ($paths as $category) {
                $categoryId = is_object($category) ? $category->getId() : $category;

                if ($this->helperData->isCropRootCategory($this->item->getStoreId())
                    && $this->isRootCategoryId($categoryId)
                ) {
                    continue;
                }

                $partPath = $this->resourceCategory->getAttributeRawValue(
                    $categoryId,
                    'name',
                    $this->storeManager->getStore($this->item->getStoreId())
                );

                if ($partPath == 'Root Catalog') {
                    continue;
                }

                $path[] = $partPath;
            }
        }

        return $path;
    }

    /**
     *
     * @return string
     */
    protected function _convertSubCategories()
    {
        $value            = '';
        $childIdsAsString = $this->item->getChildren();

        if (!$childIdsAsString) {
            return $value;
        }

        $childIds = explode(',', $childIdsAsString);

        $separator = ', ';
        $names     = [];

        foreach ($childIds as $categoryId) {
            if ($this->helperData->isCropRootCategory($this->item->getStoreId())
                && $this->isRootCategoryId($categoryId)
            ) {
                continue;
            }

            $partNames = $this->resourceCategory->getAttributeRawValue(
                $categoryId,
                'name',
                $this->storeManager->getStore($this->item->getStoreId())
            );

            if ($partNames == 'Root Catalog') {
                continue;
            }

            $names[] = $partNames;
            $names   = array_filter($names);
        }

        if (!empty($names) && is_array($names)) {
            $names = array_filter($names);
            $value = join($separator, $names);
        }

        return $value;
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
     * @return string
     */
    protected function _convertAttribute($attributeCode)
    {
        $value = '';
        if ($attribute = $this->item->getResource()->getAttribute($attributeCode)) {
            $value = $attribute->getSource()->getOptionText($this->item->getData($attributeCode));
        }
        if (!$value) {
            $value = $this->item->getData($attributeCode);
        }
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
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
     *
     * @param int $id
     * @return boolean
     */
    protected function isRootCategoryId($id)
    {
        return $this->storeManager->getStore($this->item->getStoreId())->getRootCategoryId() == $id;
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
                if (strpos(trim((string)$attribute), 'filter_') === 0) {
                    return true;
                }
            } else {
                if (strpos(trim((string)$attribute), 'filter_') !== 0) {
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

                if ($filter['name']  instanceof \Magento\Framework\Phrase) {
                    $filterName = $filter['name']->__toString();
                } else {
                    $filterName = $filter['name'];
                }

                if ($attributeCode == 'all' || $attributeCode == $filter['code']) {
                    $commonFilter[$filterName][] = $filter['label'];
                } elseif ($attributeCode == 'all_value' || $attributeCode == $filter['code'] . '_value') {
                    $value .= strip_tags($filter['label']) . ', ';
                } elseif ($attributeCode == 'all_label') {
                    $value .= $filterName . ', ';
                } elseif ($attributeCode == $filter['code'] . '_label') {
                    $value = $filterName;
                }
            }
        }

        $value = rtrim($value, ', ');

        foreach ($commonFilter as $name => $labels) {
            $value .= $name . ": " . strip_tags(implode(', ', $labels)) . '; ';
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
