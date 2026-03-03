<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

namespace MageWorx\SeoMarkup\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateFreeShippingConfigSettings implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $relatedSettingsPaths = [
            'mageworx_seo/markup/product/shipping_details/free_shipping_enabled' =>
                'mageworx_seo/markup/product/free_shipping_enabled',
            'mageworx_seo/markup/product/shipping_details/free_shipping_code'    =>
                'mageworx_seo/markup/product/free_shipping_code'
        ];

        foreach ($relatedSettingsPaths as $newPath => $oldPath) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path' => $newPath],
                ['path = ?' => $oldPath]
            );
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
}
