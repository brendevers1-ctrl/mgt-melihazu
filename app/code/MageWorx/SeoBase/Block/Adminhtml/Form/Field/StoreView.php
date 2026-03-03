<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Store\Model\StoreManagerInterface;

class StoreView extends AbstractSelect
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * StoreView constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager, array $data = [])
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    protected function getSourceOptions(): array
    {
        $options = [];
        $stores  = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $options[] = ['value' => $store->getId(), 'label' => $store->getName()];
        }

        return $options;
    }
}
