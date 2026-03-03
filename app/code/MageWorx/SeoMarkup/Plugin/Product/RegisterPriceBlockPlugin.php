<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Plugin\Product;

use Magento\Framework\App\RequestInterface;
use MageWorx\SeoMarkup\Helper\Product as HelperProduct;
use MageWorx\SeoMarkup\Observer\RemoveProductOfferMarkupObserver;
use Magento\Framework\Pricing\Render\AmountRenderInterface;
use Magento\Framework\Pricing\Render\RendererPool;


class RegisterPriceBlockPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HelperProduct
     */
    protected $helperProduct;

    /**
     * @var RemoveProductOfferMarkupObserver
     */
    protected $observer;

    /**
     * RemoveOfferMarkupPlugin constructor.
     *
     * @param RequestInterface $request
     * @param HelperProduct $helperProduct
     * @param RemoveProductOfferMarkupObserver $observer
     */
    public function __construct(
        RequestInterface $request,
        HelperProduct $helperProduct,
        RemoveProductOfferMarkupObserver $observer
    ) {
        $this->request       = $request;
        $this->helperProduct = $helperProduct;
        $this->observer      = $observer;
    }

    /**
     * @param RendererPool $subject
     * @param AmountRenderInterface $result
     * @return mixed
     */
    public function afterCreateAmountRender(RendererPool $subject, AmountRenderInterface $result)
    {
        if ($this->helperProduct->isRsEnabled() && $this->request->getFullActionName() === 'catalog_product_view') {
            $this->observer->setPriceBlockName(get_class($result));
        }

        return $result;
    }
}
