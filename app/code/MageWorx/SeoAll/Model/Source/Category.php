<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model\Source;

use MageWorx\SeoAll\Model\Source;

class Category extends Source
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $categoryTreeResource;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * @var array
     */
    protected $categoryTreeByParentId = [];

    /**
     * Category constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTreeResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTreeResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryTreeResource      = $categoryTreeResource;
        $this->storeManager              = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @param array $specialCategories
     * @param string $action disable or mark
     * @param string $markLabel
     * @param int|null $storeId
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray(
        array $specialCategories = [],
        string $action = 'disable',
        string $markLabel = '',
        int $storeId = null
    ) {
        $this->options = [['value' => '', 'label' => '']];

        foreach ($this->getCategoryTree($storeId) as $category) {
            $params['value'] = $category['value'];
            $params['label'] = $category['label'];

            if (in_array($category['value'], $specialCategories)) {

                if ($action == 'disable') {
                    $params['disabled'] = 1;
                } elseif ($action == 'mark') {
                    $params['label'] = $params['label'] . $markLabel;
                    unset($params['disabled']);
                }
            } else {
                unset($params['disabled']);
            }

            $this->options[] = $params;
        }

        return $this->options;
    }

    /**
     * @param int $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryNames(int $storeId): array
    {
        $names        = [];
        $needCropName = $storeId === \Magento\Store\Model\Store::DEFAULT_STORE_ID ? true : false;

        foreach ($this->getCategoryTree($storeId) as $category) {
            $names[$category['value']] = $needCropName ? substr($category['label'], 3) : $category['label'];
        }

        return $names;
    }

    /**
     *
     * @param Varien_Data_Tree_Node $node
     * @param array $values
     * @param int $level
     * @return array
     */
    protected function createCategoryTree($node, $values, $level = 0)
    {
        $level++;

        /* for case if category doesn't have parent category */
        if ($node == null) {
            return [];
        }

        $label = str_repeat("--/", $level - 1) . $node->getName() . ' (ID#' . $node->getId() . ')';

        $values[$node->getId()]['value']    = $node->getId();
        $values[$node->getId()]['label']    = $label;
        $values[$node->getId()]['disabled'] = true;

        foreach ($node->getChildren() as $child) {
            $values = $this->createCategoryTree($child, $values, $level);
        }

        return $values;
    }

    /**
     * @param int|null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategoryTree(int $storeId = null): array
    {
        if ($storeId === \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        } else {
            $parentId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        }

        if (isset($this->categoryTreeByParentId[$parentId])) {
            return $this->categoryTreeByParentId[$parentId];
        }

        $tree = $this->categoryTreeResource->load();
        $root = $tree->getNodeById($parentId);

        if ($root && $root->getId() == 1) {
            $root->setName(__('Root'));
        }

        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection
            ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');


        $tree->addCollectionData($categoryCollection, true);

        $this->categoryTreeByParentId[$parentId] = $this->createCategoryTree($root, []);

        return $this->categoryTreeByParentId[$parentId];
    }
}
