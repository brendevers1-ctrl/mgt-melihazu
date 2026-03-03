<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\SeoMarkup\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddSameAsAttribute implements DataPatchInterface
{
    private CategorySetupFactory $categorySetupFactory;
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
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
            'meta_same_as',
            [
                'group'            => 'Search Engine Optimization',
                'type'             => 'text',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'sameAs property',
                'input'            => 'textarea',
                'class'            => '',
                'sort_order'       => 40,
                'global'           => ScopedAttributeInterface::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'default'          => '',
                'apply_to'         => '',
                'visible_on_front' => false,
                'note'             => 'Each link must be on a new line. This setting was added by MageWorx Markup'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
