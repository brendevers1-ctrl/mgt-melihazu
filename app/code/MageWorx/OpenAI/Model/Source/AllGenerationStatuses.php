<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AllGenerationStatuses implements OptionSourceInterface
{
    /**
     * @var OptionSourceInterface
     */
    protected array $pool;

    public function __construct(
        array $pool = []
    ) {
        $this->pool = $pool;
    }

    public function toOptionArray(): array
    {
        $options = [];
        $pool    = $this->getPool();
        foreach ($pool as $sourceModel) {
            $options = array_merge($options, $sourceModel->toOptionArray());
        }

        return $options;
    }

    /**
     * @return OptionSourceInterface[]
     */
    public function getPool(): array
    {
        return $this->pool;
    }
}
