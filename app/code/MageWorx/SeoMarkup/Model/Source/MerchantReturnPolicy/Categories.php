<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source\MerchantReturnPolicy;

class Categories extends \MageWorx\SeoMarkup\Model\Source
{
    const FINITE_RETURN_WINDOW = 'MerchantReturnFiniteReturnWindow';
    const NOT_PERMITTED        = 'MerchantReturnNotPermitted';
    const UNLIMITED_WINDOW     = 'MerchantReturnUnlimitedWindow';
    const UNSPECIFIED          = 'MerchantReturnUnspecified';

    /**
     * {@inheritDoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => self::FINITE_RETURN_WINDOW, 'label' => 'Finite Return Window'],
            ['value' => self::NOT_PERMITTED, 'label' => 'Not Permitted'],
            ['value' => self::UNLIMITED_WINDOW, 'label' => 'Unlimited Window'],
            ['value' => self::UNSPECIFIED, 'label' => 'Unspecified']
        ];
    }
}
