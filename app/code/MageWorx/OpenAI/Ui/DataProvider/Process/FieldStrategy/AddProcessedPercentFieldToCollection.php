<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\DataProvider\Process\FieldStrategy;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

class AddProcessedPercentFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritDoc
     */
    public function addField(Collection $collection, $field, $alias = null): void
    {
        /** @var \MageWorx\OpenAI\Model\ResourceModel\QueueProcess\Collection $collection */
        $collection->addProcessedPercentColumn();
    }
}
