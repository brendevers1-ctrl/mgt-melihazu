<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Observer;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreResolver;

class RemoveProductOfferMarkupObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageWorx\SeoMarkup\Model\MarkupCleaner
     */
    protected $markupCleaner;

    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     * @var string
     */
    protected $priceBlockName = '';

    /**
     * RemoveProductOfferMarkupObserver constructor.
     *
     * @param StoreResolver $storeResolver
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     * @param \MageWorx\SeoMarkup\Model\MarkupCleaner $markupCleaner
     */
    public function __construct(
        StoreResolver $storeResolver,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct,
        \MageWorx\SeoMarkup\Model\MarkupCleaner $markupCleaner
    ) {
        $this->storeResolver = $storeResolver;
        $this->helperProduct = $helperProduct;
        $this->markupCleaner = $markupCleaner;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->priceBlockName
            && $this->helperProduct->isRsEnabled($this->storeResolver->getCurrentStoreId())
            && get_class($observer->getBlock()) === $this->priceBlockName
        ) {
            /** @var \Magento\Framework\View\Element\Template $block */
            $transport = $observer->getTransport();
            $html      = $this->markupCleaner->removeOfferMarkup($transport->getHtml());
            $transport->setHtml($html);
        }
    }

    /**
     * @param string $blockName
     */
    public function setPriceBlockName($blockName)
    {
        $this->priceBlockName = $blockName;
    }
}
