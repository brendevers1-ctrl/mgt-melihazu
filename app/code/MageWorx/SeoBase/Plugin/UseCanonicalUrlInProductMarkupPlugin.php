<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Plugin;

use MageWorx\SeoBase\Helper\Data as Helper;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\SeoBase\Model\CanonicalFactory;
use Magento\Framework\View\Layout;
use MageWorx\SeoMarkup\Helper\DataProvider\Product as ProductDataProvider;

class UseCanonicalUrlInProductMarkupPlugin
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var CanonicalFactory
     */
    protected $canonicalFactory;

    /**
     * UseCanonicalUrlInProductMarkupPlugin constructor.
     *
     * @param Helper $helper
     * @param PageConfig $pageConfig
     * @param CanonicalFactory $canonicalFactory
     */
    public function __construct(
        Helper $helper,
        PageConfig $pageConfig,
        CanonicalFactory $canonicalFactory
    ) {
        $this->helper           = $helper;
        $this->pageConfig       = $pageConfig;
        $this->canonicalFactory = $canonicalFactory;
    }

    /**
     * @param ProductDataProvider $subject
     * @param callable $proceed
     * @param mixed ...$args
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundGetProductCanonicalUrl(ProductDataProvider $subject, callable $proceed, ...$args)
    {
        if ($this->helper->isDisableCanonicalByRobots()
            && stripos($this->pageConfig->getRobots(), 'noindex') !== false
        ) {
            return $proceed(...$args);
        }

        $canonicalModel = $this->canonicalFactory->get('catalog_product_view');
        $canonicalUrl   = $canonicalModel->getCanonicalUrl();

        if ($canonicalUrl) {
            return $canonicalUrl;
        }

        return $proceed(...$args);
    }
}