<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Source\Cron;

use Magento\Framework\Data\OptionSourceInterface;

class Frequency implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected static array $options = [];

    public const CRON_EVERY_MINUTE       = '*';
    public const CRON_EVERY_FIVE_MINUTES = '*/5';
    public const CRON_EVERY_30_MINUTES   = '*/30';
    public const CRON_DISABLED           = '0';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (empty(self::$options)) {
            self::$options = [
                ['label' => __('Every minute'), 'value' => self::CRON_EVERY_MINUTE],
                ['label' => __('Every 5 minutes'), 'value' => self::CRON_EVERY_FIVE_MINUTES],
                ['label' => __('Every 30 minutes'), 'value' => self::CRON_EVERY_30_MINUTES],
                ['label' => __('Disabled'), 'value' => self::CRON_DISABLED]
            ];
        }

        return self::$options;
    }
}
