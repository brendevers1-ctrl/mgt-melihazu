<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoMarkup\Model\Source\MerchantReturnPolicy;

class Fees extends \MageWorx\SeoMarkup\Model\Source
{
    const FREE_RETURN                         = 'FreeReturn';
    const ORIGINAL_SHIPPING_FEES              = 'OriginalShippingFees';
    const RESTOCKING_FEES                     = 'RestockingFees';
    const RETURN_FEES_CUSTOMER_RESPONSIBILITY = 'ReturnFeesCustomerResponsibility';
    const RETURN_SHIPPING_FEES                = 'ReturnShippingFees';

    /**
     * {@inheritDoc}
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => self::FREE_RETURN, 'label' => 'Free Return'],
            ['value' => self::ORIGINAL_SHIPPING_FEES, 'label' => 'Original Shipping Fees'],
            ['value' => self::RESTOCKING_FEES, 'label' => 'Restocking Fees'],
            ['value' => self::RETURN_FEES_CUSTOMER_RESPONSIBILITY, 'label' => 'Return Fees Customer Responsibility'],
            ['value' => self::RETURN_SHIPPING_FEES, 'label' => 'Return Shipping Fees']
        ];
    }
}
