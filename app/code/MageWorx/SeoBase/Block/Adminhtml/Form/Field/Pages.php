<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use MageWorx\SeoBase\Model\Source\Hreflangs\PageTypes as PageTypesOptions;

class Pages extends AbstractSelect
{
    /**
     * @var PageTypesOptions
     */
    protected $pageTypesOptions;

    /**
     * Pages constructor.
     *
     * @param Context $context
     * @param PageTypesOptions $pageTypesOptions
     * @param array $data
     */
    public function __construct(Context $context, PageTypesOptions $pageTypesOptions, array $data = [])
    {
        parent::__construct($context, $data);
        $this->pageTypesOptions = $pageTypesOptions;
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        $this->setClass('pages');
        $this->setExtraParams('multiple="multiple"');
        return parent::_toHtml();
    }

    /**
     * @inheritDoc
     */
    protected function getSourceOptions(): array
    {
        return $this->pageTypesOptions->toOptionArray();
    }
}
