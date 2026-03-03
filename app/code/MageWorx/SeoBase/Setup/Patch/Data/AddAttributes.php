<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var Default value fot "meta_robots" attribute
     */
    const META_ROBOTS_DEFAULT_VALUE = '';

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'meta_robots',
            [
                'group'            => 'Search Engine Optimization',
                'type'             => 'varchar',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'Meta Robots',
                'input'            => 'select',
                'class'            => '',
                'source'           => \MageWorx\SeoAll\Model\Source\MetaRobots::class,
                'global'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => self::META_ROBOTS_DEFAULT_VALUE,
                'apply_to'         => '',
                'visible_on_front' => false,
                'note'             => 'This setting was added by MageWorx SEO Suite'
            ]
        );

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'meta_robots',
            [
                'group'            => 'Search Engine Optimization',
                'type'             => 'varchar',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'Meta Robots',
                'input'            => 'select',
                'class'            => '',
                'source'           => \MageWorx\SeoAll\Model\Source\MetaRobots::class,
                'global'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => self::META_ROBOTS_DEFAULT_VALUE,
                'apply_to'         => '',
                'visible_on_front' => false,
                'sort_order'       => 9,
                'note'             => 'This setting was added by MageWorx SEO Suite'
            ]
        );

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cross_domain_store',
            [
                'group'            => 'Search Engine Optimization',
                'type'             => 'int',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'Cross Domain Store',
                'input'            => 'select',
                'class'            => '',
                'source'           => \MageWorx\SeoBase\Model\Source\CrossDomainStore::class,
                'global'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => '',
                'apply_to'         => '',
                'visible_on_front' => false,
                'note'             => 'This setting was added by MageWorx SEO Suite'
            ]
        );


        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cross_domain_url',
            [
                'group' => 'Search Engine Optimization',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Cross Domain URL',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'apply_to' => '',
                'frontend_class' => 'validate-url',
                'visible_on_front' => false,
                'note' => 'This setting was added by MageWorx SEO Suite'
            ]
        );
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
        return '1.0.0';
    }
}
