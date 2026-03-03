<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\SeoAll\Helper\MagentoVersion;

class PreviewSearchResult extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MagentoVersion
     */
    protected $helperVersion;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * PreviewSearchResult constructor.
     *
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param MagentoVersion $helperVersion
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        MagentoVersion $helperVersion,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->locator       = $locator;
        $this->storeManager  = $storeManager;
        $this->helperVersion = $helperVersion;
        $this->scopeConfig   = $scopeConfig;
    }

    /**
     * @param array $meta
     * @return array
     * @throws NoSuchEntityException
     */
    public function modifyMeta(array $meta)
    {
        if (isset($meta['search-engine-optimization']['children'])) {
            $meta = array_replace_recursive(
                $meta,
                [
                    'search-engine-optimization' => [
                        'children' => $this->getAdditionalMeta($meta)
                    ]
                ]
            );
        }

        return $meta;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getAdditionalMeta(&$meta)
    {
        $additionalMeta = [];

        $this->addUrlKeyAdditionalMeta($additionalMeta, $meta);
        $this->addMetaTitleAdditionalMeta($additionalMeta, $meta);
        $this->addMetaDescriptionAdditionalMeta($additionalMeta, $meta);

        $additionalMeta['container_preview_search_result'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => 'container',
                        'componentType' => 'container',
                        'sortOrder'     => 0,
                    ],
                ],
            ],
            'children'  => [
                'preview_url'         => $this->getPreviewUrlConfig(10),
                'preview_title'       => $this->getPreviewTitleConfig(20),
                'preview_description' => $this->getPreviewDescriptionConfig(30),
            ],
        ];

        return $additionalMeta;
    }

    /**
     * @param array $additionalMeta
     * @param array $meta
     * @return void
     */
    protected function addUrlKeyAdditionalMeta(&$additionalMeta, &$meta)
    {
        if (isset($meta['search-engine-optimization']['children']['container_url_key']['children']['url_key'])) {
            $additionalMeta['container_url_key'] = [
                'children' => [
                    'url_key' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'valueUpdate' => 'keyup'
                                ],
                            ],
                        ],
                    ],
                ]
            ];
        }
    }

    /**
     * @param array $additionalMeta
     * @param array $meta
     * @return void
     */
    protected function addMetaTitleAdditionalMeta(&$additionalMeta, &$meta)
    {
        if (isset($meta['search-engine-optimization']['children']['container_meta_title']['children']['meta_title'])) {
            $additionalMeta['container_meta_title'] = [
                'children' => [
                    'meta_title' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'valueUpdate' => 'keyup'
                                ],
                            ],
                        ],
                    ],
                ]
            ];
        }
    }

    /**
     * @param array $additionalMeta
     * @param array $meta
     * @return void
     */
    protected function addMetaDescriptionAdditionalMeta(&$additionalMeta, &$meta)
    {
        $container = 'container_meta_description';

        if (isset($meta['search-engine-optimization']['children'][$container]['children']['meta_description'])) {
            $additionalMeta[$container] = [
                'children' => [
                    'meta_description' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'valueUpdate' => 'keyup'
                                ],
                            ],
                        ],
                    ],
                ]
            ];
        }
    }

    /**
     * @param int $sortOrder
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getPreviewUrlConfig($sortOrder)
    {
        $storeId = $this->getStoreId();
        $suffix  = (string)$this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($storeId === Store::DEFAULT_STORE_ID) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $imports = ['prepareValue' => '${ $.provider }:data.product.url_key'];

        if ($this->helperVersion->checkModuleVersion('Magento_Ui', '101.1.4')) {
            $imports['__disableTmpl'] = ['prepareValue' => false];
        }

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-url',
                        'imports'           => $imports,
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_url',
                        'baseUrl'           => $this->storeManager->getStore($storeId)->getBaseUrl(),
                        'suffix'            => $suffix,
                        'label'             => __('Preview')
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getPreviewTitleConfig($sortOrder)
    {
        $imports = ['prepareValue' => '${ $.provider }:data.product.meta_title'];

        if ($this->helperVersion->checkModuleVersion('Magento_Ui', '101.1.4')) {
            $imports['__disableTmpl'] = ['prepareValue' => false];
        }

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-title',
                        'imports'           => $imports,
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_title',
                        'label'             => ' '
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getPreviewDescriptionConfig($sortOrder)
    {
        $imports = ['prepareValue' => '${ $.provider }:data.product.meta_description'];

        if ($this->helperVersion->checkModuleVersion('Magento_Ui', '101.1.4')) {
            $imports['__disableTmpl'] = ['prepareValue' => false];
        }

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'elementTmpl'       => 'ui/form/element/text',
                        'component'         => 'MageWorx_SeoBase/js/form/element/preview-description',
                        'imports'           => $imports,
                        'sortOrder'         => $sortOrder,
                        'additionalClasses' => 'preview preview_description',
                        'label'             => ' '
                    ],
                ],
            ],
        ];
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->locator->getStore()->getId();
    }
}
