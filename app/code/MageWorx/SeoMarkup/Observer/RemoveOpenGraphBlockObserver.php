<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreResolver;
use MageWorx\SeoMarkup\Model\OpenGraphConfigProvider;

class RemoveOpenGraphBlockObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @var OpenGraphConfigProvider
     */
    protected $openGraphConfigProvider;

    /**
     * RemoveOpenGraphBlockObserver constructor.
     *
     * @param StoreResolver $storeResolver
     * @param OpenGraphConfigProvider $openGraphConfigProvider
     */
    public function __construct(StoreResolver $storeResolver, OpenGraphConfigProvider $openGraphConfigProvider)
    {
        $this->storeResolver           = $storeResolver;
        $this->openGraphConfigProvider = $openGraphConfigProvider;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $observer->getBlock();

        if ($block->getNameInLayout() == 'opengraph.general'
            && $this->openGraphConfigProvider->isEnabledForProduct((int)$this->storeResolver->getCurrentStoreId())
        ) {
            $block->setTemplate('');
        }
    }
}
