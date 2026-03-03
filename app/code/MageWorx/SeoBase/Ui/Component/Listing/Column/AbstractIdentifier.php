<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Ui\Component\Listing\Column;

abstract class AbstractIdentifier extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \MageWorx\SeoAll\Model\Source\Category
     */
    protected $categoryOptions;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var array|null
     */
    protected $productNames;

    /**
     * @var array|null
     */
    protected $pageTitles;

    /**
     * @var string
     */
    protected $entityType = '';

    /**
     * @var string
     */
    protected $entityIdentifier = '';

    /**
     * AbstractIdentifier constructor.
     *
     * @param \MageWorx\SeoAll\Model\Source\Category $categoryOptions
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \MageWorx\SeoAll\Model\Source\Category $categoryOptions,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->pageCollectionFactory    = $pageCollectionFactory;
        $this->categoryOptions          = $categoryOptions;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function getProductOptions(array $productIds)
    {
        if ($this->productNames === null) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addIdFilter($productIds);
            $collection->addAttributeToSelect('name');
            $this->productNames = $collection->toOptionHash();
        }

        return $this->productNames;
    }

    /**
     * @param array $pageIds
     * @return array
     */
    protected function getPageOptions(array $pageIds)
    {
        if ($this->pageTitles === null) {
            /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
            $collection = $this->pageCollectionFactory->create();

            $collection->addFieldToFilter($collection->getIdFieldName(), $pageIds);
            $collection->addFieldToSelect($collection->getIdFieldName());
            $collection->addFieldToSelect(\Magento\Cms\Api\Data\PageInterface::TITLE);

            $this->pageTitles = [];
            foreach ($collection as $item) {
                $this->pageTitles[$item->getData($collection->getIdFieldName())] =
                    $item->getData(\Magento\Cms\Api\Data\PageInterface::TITLE);
            }
        }

        return $this->pageTitles;
    }

    /**
     * @param array $item
     * @return void
     */
    protected function modifyCategoryIdentifier(&$item)
    {
        $categoryOptions = $this->categoryOptions->toArray();
        if (!empty($categoryOptions[$item[$this->entityIdentifier]])) {
            $item[$this->getData('name')] =
                str_replace('(ID#', '<br> (ID#', $categoryOptions[$item[$this->entityIdentifier]]);
        } else {
            $item[$this->getData('name')] =
                __('UNKNOWN CATEGORY') . "<br>" . ' (ID#' . $item[$this->entityIdentifier] . ')';
        }
    }

    /**
     * @param array $item
     * @param array $productIds
     * @return void
     */
    protected function modifyProductIdentifier(&$item, $productIds)
    {
        $productOptions = $this->getProductOptions($productIds);
        if (!empty($productOptions[$item[$this->entityIdentifier]])) {
            $item[$this->getData('name')] =
                $productOptions[$item[$this->entityIdentifier]] .
                "<br>" .
                ' (ID#' . $item[$this->entityIdentifier] . ')';
        } else {
            $item[$this->getData('name')] =
                __('UNKNOWN PRODUCT') . "<br>" . ' (ID#' . $item[$this->entityIdentifier] . ')';
        }
    }

    /**
     * @param array $item
     * @param array $pageIds
     * @return void
     */
    protected function modifyPageIdentifier(&$item, $pageIds)
    {
        $pageOptions = $this->getPageOptions($pageIds);
        if (!empty($pageOptions[$item[$this->entityIdentifier]])) {
            $item[$this->getData('name')] =
                $pageOptions[$item[$this->entityIdentifier]] . "<br>" . ' (ID#' . $item[$this->entityIdentifier] . ')';
        } else {
            $item[$this->getData('name')] =
                __('UNKNOWN PAGE') . "<br>" . ' (ID#' . $item[$this->entityIdentifier] . ')';
        }
    }
}
