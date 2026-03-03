<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Setup\Patch\Data;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\Store;
use MageWorx\SeoAll\Helper\LinkFieldResolver;

class AddAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Default value for "use_in_crosslinking" attribute
     */
    const USE_IN_CROSSLINK_DEFAULT_VALUE = 1;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var LinkFieldResolver
     */
    protected $linkFieldResolver;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        LinkFieldResolver $linkFieldResolver
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->linkFieldResolver    = $linkFieldResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $attributeCode = 'use_in_crosslinking';
        $catalogSetup  = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $catalogSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'group'            => 'Search Engine Optimization',
                'type'             => 'int',
                'backend'          => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend'         => '',
                'label'            => 'Use in Cross Linking',
                'input'            => 'select',
                'class'            => '',
                'source'           => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global'           => Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => self::USE_IN_CROSSLINK_DEFAULT_VALUE,
                'apply_to'         => '',
                'visible_on_front' => false,
                'note'             => 'This setting was added by MageWorx SEO Cross Links'
            ]
        );

        $connection                  = $this->moduleDataSetup->getConnection();
        $attributeTableFieldsProduct = $connection->describeTable(
            $this->moduleDataSetup->getTable('catalog_product_entity_int')
        );

        $productTypeId          = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $selectProductAttribute = $connection->select();

        $selectProductAttribute
            ->from(['ea' => $this->moduleDataSetup->getTable('eav_attribute')], ['attribute_id'])
            ->where("`entity_type_id` = '" . $productTypeId . "'")
            ->where("attribute_code = ?", $attributeCode);

        $productAttributeId = $connection->fetchOne($selectProductAttribute);

        if (is_numeric($productAttributeId)) {
            $linkField = $this->linkFieldResolver->getLinkField(ProductInterface::class, 'entity_id');

            $productAttributeValueInsert = $connection->select()->from(
                ['e1' => $this->moduleDataSetup->getTable('catalog_product_entity')],
                array_merge(
                    $attributeTableFieldsProduct,
                    [
                        'value_id'     => new \Zend_Db_Expr('NULL'),
                        'attribute_id' => new \Zend_Db_Expr($productAttributeId),
                        'store_id'     => new \Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                        $linkField     => 'e1.' . $linkField,
                        'value'        => new \Zend_Db_Expr(self::USE_IN_CROSSLINK_DEFAULT_VALUE),
                    ]
                )
            )->where(
                'e1.' . $linkField . ' NOT IN(' . new \Zend_Db_Expr(
                    "SELECT `" . $linkField . "` FROM " . $this->moduleDataSetup->getTable(
                        'catalog_product_entity_int'
                    ) .
                    " WHERE `store_id` = 0 AND `attribute_id` = " . $productAttributeId . ")"
                )
            )->order(
                ['e1.' . $linkField],
                Select::SQL_ASC
            )->insertFromSelect(
                $this->moduleDataSetup->getTable('catalog_product_entity_int')
            );
            $this->moduleDataSetup->run($productAttributeValueInsert);
        }

        $catalogSetup->addAttribute(
            Category::ENTITY,
            $attributeCode,
            [
                'group'            => 'General Information',
                'type'             => 'int',
                'backend'          => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend'         => '',
                'label'            => 'Use in Cross Linking',
                'input'            => 'select',
                'class'            => '',
                'source'           => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global'           => Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => self::USE_IN_CROSSLINK_DEFAULT_VALUE,
                'apply_to'         => '',
                'visible_on_front' => false,
                'sort_order'       => 6,
                'note'             => 'This setting was added by MageWorx SEO Cross Links'
            ]
        );

        $attributeTableFieldsCategory = $connection->describeTable(
            $this->moduleDataSetup->getTable('catalog_category_entity_int')
        );

        $categoryTypeId          = $catalogSetup->getEntityTypeId(Category::ENTITY);
        $selectCategoryAttribute = $connection->select();

        $selectCategoryAttribute
            ->from(
                ['ea' => $this->moduleDataSetup->getTable('eav_attribute')],
                ['attribute_id']
            )
            ->where("`entity_type_id` = '" . $categoryTypeId . "'")
            ->where("attribute_code = ?", $attributeCode);

        $categoryAttributeId = $connection->fetchOne($selectCategoryAttribute);

        if (is_numeric($categoryAttributeId)) {
            $categoryLinkField = $this->linkFieldResolver->getLinkField(CategoryInterface::class, 'entity_id');

            $itemsInsert = $connection->select()->from(
                ['e1' => $this->moduleDataSetup->getTable('catalog_category_entity')],
                array_merge(
                    $attributeTableFieldsCategory,
                    [
                        'value_id'         => new \Zend_Db_Expr('NULL'),
                        'attribute_id'     => new \Zend_Db_Expr($categoryAttributeId),
                        'store_id'         => new \Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                        $categoryLinkField => 'e1.' . $categoryLinkField,
                        'value'            => new \Zend_Db_Expr(self::USE_IN_CROSSLINK_DEFAULT_VALUE),
                    ]
                )
            )->where(
                'e1.' . $categoryLinkField . ' NOT IN(' . new \Zend_Db_Expr(
                    "SELECT `" . $categoryLinkField . "` FROM " . $this->moduleDataSetup->getTable(
                        'catalog_category_entity_int'
                    ) .
                    " WHERE `store_id` = 0 AND `attribute_id` = " . $categoryAttributeId . ")"
                )
            )->order(
                ['e1.' . $categoryLinkField],
                Select::SQL_ASC
            )->insertFromSelect(
                $this->moduleDataSetup->getTable('catalog_category_entity_int')
            );

            $this->moduleDataSetup->run($itemsInsert);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return '2.0.3';
    }
}
