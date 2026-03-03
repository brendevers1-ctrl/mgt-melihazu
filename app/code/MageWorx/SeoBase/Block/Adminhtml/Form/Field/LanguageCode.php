<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use MageWorx\SeoBase\Model\Source\Hreflangs\LanguageCode as LanguageCodeOptions;

class LanguageCode extends AbstractSelect
{
    /**
     * @var LanguageCodeOptions
     */
    protected $languageCodeOptions;

    /**
     * LanguageCode constructor.
     *
     * @param Context $context
     * @param LanguageCodeOptions $languageCodeOptions
     * @param array $data
     */
    public function __construct(Context $context, LanguageCodeOptions $languageCodeOptions, array $data = [])
    {
        parent::__construct($context, $data);
        $this->languageCodeOptions = $languageCodeOptions;
    }

    /**
     * @inheritDoc
     */
    protected function getSourceOptions(): array
    {
        return $this->languageCodeOptions->toOptionArray();
    }
}
