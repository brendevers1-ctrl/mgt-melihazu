<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateConfigSettings implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->updateOpenGraphSettings();
        $this->updateTwitterCardsSettings();
        $this->duplicateProductDescriptionCodeSettingValue();
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
        return '2.1.0';
    }

    protected function updateOpenGraphSettings(): void
    {
        $relatedSettingsPaths = [
            'mageworx_seo/markup/open_graph/enabled_for_product'   => 'mageworx_seo/markup/product/og_enabled',
            'mageworx_seo/markup/open_graph/enabled_for_category'  => 'mageworx_seo/markup/category/og_enabled',
            'mageworx_seo/markup/open_graph/enabled_for_page'      => 'mageworx_seo/markup/page/og_enabled',
            'mageworx_seo/markup/open_graph/enabled_for_home_page' => 'mageworx_seo/markup/website/og_enabled'
        ];

        foreach ($relatedSettingsPaths as $newPath => $oldPath) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path' => $newPath],
                ['path = ?' => $oldPath]
            );
        }
    }

    protected function updateTwitterCardsSettings(): void
    {
        $relatedSettingsPaths = [
            'mageworx_seo/markup/tw_cards/username'              => 'mageworx_seo/markup/common/tw_username',
            'mageworx_seo/markup/tw_cards/enabled_for_product'   => 'mageworx_seo/markup/product/tw_enabled',
            'mageworx_seo/markup/tw_cards/enabled_for_category'  => 'mageworx_seo/markup/category/tw_enabled',
            'mageworx_seo/markup/tw_cards/enabled_for_page'      => 'mageworx_seo/markup/page/tw_enabled',
            'mageworx_seo/markup/tw_cards/enabled_for_home_page' => 'mageworx_seo/markup/website/tw_enabled',
        ];

        foreach ($relatedSettingsPaths as $newPath => $oldPath) {
            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path' => $newPath],
                ['path = ?' => $oldPath]
            );
        }
    }

    protected function duplicateProductDescriptionCodeSettingValue(): void
    {
        $newPaths   = [
            'mageworx_seo/markup/open_graph/product_description_code',
            'mageworx_seo/markup/tw_cards/product_description_code'
        ];
        $connection = $this->moduleDataSetup->getConnection();
        $select     = $connection->select();
        $select
            ->from($this->moduleDataSetup->getTable('core_config_data'))
            ->where('path IN(?)', $newPaths);

        if ($connection->fetchOne($select)) {
            return;
        }

        $select = $connection->select();
        $select
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['scope', 'scope_id', 'path', 'value'])
            ->where('path = ?', 'mageworx_seo/markup/product/description_code');

        $rows = $connection->fetchAll($select);
        $data = [];

        foreach ($rows as $row) {
            foreach ($newPaths as $path) {
                $row['path'] = $path;
                $data[]      = $row;
            }
        }

        if ($data) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('core_config_data'), $data);
        }
    }
}
