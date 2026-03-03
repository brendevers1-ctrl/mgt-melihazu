<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source\MerchantReturnPolicy;

class Methods extends \MageWorx\SeoMarkup\Model\Source
{
    /**
     * {@inheritDoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => 'ReturnAtKiosk', 'label' => 'At Kiosk'],
            ['value' => 'ReturnByMail', 'label' => 'By Mail'],
            ['value' => 'ReturnInStore', 'label' => 'In Store']
        ];
    }
}
