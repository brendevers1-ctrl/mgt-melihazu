<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Controller\Adminhtml\Templateproduct;

use MageWorx\SeoXTemplates\Controller\Adminhtml\Templateproduct\Products as TemplateProductsController;
use MageWorx\SeoXTemplates\Model\Template\ProductFactory as TemplateProductFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;


class Productsgrid extends TemplateProductsController
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Productsgrid constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param ResultLayoutFactory $resultLayoutFactory
     * @param Registry $registry
     * @param TemplateProductFactory $templateProductFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        Registry $registry,
        TemplateProductFactory $templateProductFactory
    ) {
        parent::__construct(
            $resultLayoutFactory,
            $registry,
            $templateProductFactory,
            $context
        );
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory    = $layoutFactory;
    }

    /**
     * Display list of products related to current template
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $this->initTemplateProduct();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \MageWorx\SeoXTemplates\Block\Adminhtml\Template\Product\Edit\Tab\Products::class,
                'template.product.grid'
            )->toHtml()
        );
    }
}
