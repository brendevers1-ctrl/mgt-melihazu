<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CronFrequency extends Value
{
    public const CRON_STRING_PATH = 'mageworx_openai/main_settings/cron/cron_frequency_value';
    /**
     * @var ValueFactory
     */
    protected ValueFactory $configValueFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        ValueFactory         $configValueFactory,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save handler
     *
     * @return $this
     * @throws LocalizedException
     */
    public function afterSave()
    {
        $frequencyMinutes = $this->getData('value') ?: \MageWorx\OpenAI\Model\Source\Cron\Frequency::CRON_DISABLED;

        if ($frequencyMinutes === \MageWorx\OpenAI\Model\Source\Cron\Frequency::CRON_DISABLED) {
            // Returns February 30, which does not exist. This effectively disables the cron job.
            $cronExprString = '0 0 30 2 *';
        } else {
            $cronExprArray = [
                $frequencyMinutes, # Minute
                '*',               # Hour
                '*',               # Day of the Month
                '*',               # Month of the Year
                '*',               # Day of the Week
            ];

            $cronExprString = join(' ', $cronExprArray);
        }

        try {
            /** @var $configValue ValueInterface|Value */
            $configValue = $this->configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)
                        ->setPath(self::CRON_STRING_PATH)
                        ->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the Cron expression.'));
        }

        return parent::afterSave();
    }
}
