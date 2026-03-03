<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use MageWorx\OpenAI\Model\Source\Cron\Frequency;

class DisableCronjob implements DataPatchInterface
{
    protected WriterInterface      $configWriter;
    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        WriterInterface      $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig  = $scopeConfig;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Apply the patch.
     * Disable cronjob if nothing was set in the configuration settings 'mageworx_openai/main_settings/api_key'.
     * Setting the 'mageworx_openai/main_settings/cron/cron_frequency' to '0' (disabled).
     * Setting the 'mageworx_openai/main_settings/cron/cron_frequency_value' to '0 0 30 2 *' (never run).
     */
    public function apply()
    {
        $apiKey = $this->scopeConfig->getValue('mageworx_openai/main_settings/api_key');

        if (empty($apiKey)) {
            $this->configWriter->save('mageworx_openai/main_settings/cron/cron_frequency', Frequency::CRON_DISABLED);
            $this->configWriter->save('mageworx_openai/main_settings/cron/cron_frequency_value', '0 0 30 2 *');
        }

        return $this;
    }
}
