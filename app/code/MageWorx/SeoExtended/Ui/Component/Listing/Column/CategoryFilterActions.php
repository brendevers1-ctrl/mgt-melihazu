<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoExtended\Ui\Component\Listing\Column;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class CategoryFilterActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'mageworx_seoextended/categoryfilter/edit';

    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'mageworx_seoextended/categoryfilter/delete';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductAttributeCollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * @var CategoryResourceModel
     */
    protected $categoryResourceModel;

    /**
     * @var array|null
     */
    protected $attributeNames;

    /**
     * @var array
     */
    protected $categoryNames = [];

    /**
     * CategoryFilterActions constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param ProductAttributeCollectionFactory $productAttributeCollectionFactory
     * @param CategoryResourceModel $categoryResourceModel
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        ProductAttributeCollectionFactory $productAttributeCollectionFactory,
        CategoryResourceModel $categoryResourceModel,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder                        = $urlBuilder;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->categoryResourceModel             = $categoryResourceModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $attributeName = '';
                    $categoryName  = '';

                    if (isset($item['attribute_id'])) {
                        $attributeName = $this->getAttributeName((int)$item['attribute_id']);
                    }

                    if (isset($item['category_id'])) {
                        $categoryName = $this->getCategoryName((int)$item['category_id']);
                    }

                    $confirmMessage = __(
                        'Are you sure you want to delete the SEO Category Filter for "%1" and Category "%2" ?',
                        $attributeName,
                        $categoryName
                    );

                    $item[$this->getData('name')] = [
                        'edit'   => [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href'    => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['id']
                                ]
                            ),
                            'label'   => __('Delete'),
                            'confirm' => [
                                'title'   => __('Delete Category Filter'),
                                'message' => $confirmMessage
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $attributeId
     * @return string
     */
    protected function getAttributeName(int $attributeId): string
    {
        if (!isset($this->attributeNames)) {
            $this->prepareAttributeNames();
        }

        if (isset($this->attributeNames[$attributeId])) {
            return $this->attributeNames[$attributeId];
        }

        return '';
    }

    /**
     * @return void
     */
    protected function prepareAttributeNames(): void
    {
        $this->attributeNames = [];
        $collection           = $this->productAttributeCollectionFactory->create();
        $collection->addFieldToSelect(
            [ProductAttributeInterface::ATTRIBUTE_ID, ProductAttributeInterface::FRONTEND_LABEL]
        );

        foreach ($collection->getData() as $datum) {
            $attributeId                        = $datum[ProductAttributeInterface::ATTRIBUTE_ID];
            $this->attributeNames[$attributeId] = $datum[ProductAttributeInterface::FRONTEND_LABEL];
        }
    }

    /**
     * @param int $categoryId
     * @return string
     */
    protected function getCategoryName(int $categoryId): string
    {
        if (isset($this->categoryNames[$categoryId])) {
            return $this->categoryNames[$categoryId];
        }

        $this->categoryNames[$categoryId] = (string)$this->categoryResourceModel->getAttributeRawValue(
            $categoryId,
            \Magento\Catalog\Api\Data\CategoryInterface::KEY_NAME,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        return $this->categoryNames[$categoryId];
    }
}
