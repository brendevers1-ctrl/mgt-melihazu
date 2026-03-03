<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ProductMetadataInterface;

class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Product constructor.
     *
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Category $catalogCategory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes
     * @param ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        CollectionFactory $categoryCollectionFactory,
        Category $catalogCategory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        ProductMetadataInterface $productMetadata,
        $data = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $categoryCollectionFactory,
            $catalogCategory,
            $eventManager,
            $setFactory,
            $typeFactory,
            $defaultAttributes,
            $data
        );
        $this->productMetadata = $productMetadata;
    }

    /**
     * Retrieve array with pairs: store_id => value
     * for text-type attribute
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute $attribute
     * @return array
     */
    public function getAttributeValues($attribute, $entityId)
    {
        $tableName  = $attribute->getBackendTable();
        $connection = $this->getConnection();
        $select     = $connection->select();

        $edition = mb_strtolower($this->productMetadata->getEdition());
        if ($edition === 'enterprise' || $edition == 'b2b') {
            $linkField = $this->getLinkField();

            $select
                ->from(['attribute_table' => $tableName], ['store_id', 'value'])
                ->join(
                    ['entity_table' => $this->getTable('catalog_product_entity')],
                    "entity_table.{$linkField} = attribute_table.{$linkField}",
                    []
                )
                ->where('attribute_table.attribute_id = ?', $attribute->getId())
                ->where('entity_table.entity_id = ?', $entityId);
        } else {
            $select
                ->from($tableName, ['store_id', 'value'])
                ->where('attribute_id = ?', $attribute->getId())
                ->where('entity_id = ?', $entityId);
        }

        $data = [];

        foreach ($connection->fetchAll($select) as $row) {
            $data[$row['store_id']] = $row['value'];
        }

        return $data;
    }
}
