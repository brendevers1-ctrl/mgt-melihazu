<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

use MageWorx\SeoBase\Model\Source\Hreflangs\CountryCode as CountryCodeOptions;
use Magento\Framework\View\Element\Context;

class CountryCode extends AbstractSelect
{
    /**
     * @var CountryCodeOptions
     */
    protected $countryCodeOptions;

    /**
     * CountryCode constructor.
     *
     * @param Context $context
     * @param CountryCodeOptions $countryCodeOptions
     * @param array $data
     */
    public function __construct(Context $context, CountryCodeOptions $countryCodeOptions, array $data = [])
    {
        parent::__construct($context, $data);
        $this->countryCodeOptions = $countryCodeOptions;
    }

    /**
     * @inheritDoc
     */
    protected function getSourceOptions(): array
    {
        return $this->countryCodeOptions->toOptionArray();
    }
}
