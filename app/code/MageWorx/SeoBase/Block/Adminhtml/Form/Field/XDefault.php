<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

class XDefault extends AbstractSelect
{
    /**
     * @inheritDoc
     */
    protected function getSourceOptions(): array
    {
        return [
            ['value' => 0, 'label' => 'No'],
            ['value' => 1, 'label' => 'Yes']
        ];
    }
}
