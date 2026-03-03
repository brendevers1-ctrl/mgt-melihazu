<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Plugin;

use MageWorx\SeoMarkup\Block\Head\SocialMarkup\Category as SocialMarkupCategory;
use MageWorx\SeoBase\Helper\Data as Helper;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\SeoBase\Model\CanonicalFactory;

class UseCanonicalUrlInCategorySocialMarkupPlugin
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
     * UseCanonicalUrlInCategorySocialMarkupPlugin constructor.
     *
     * @param Helper $helper
     * @param PageConfig $pageConfig
     * @param CanonicalFactory $canonicalFactory
     */
    public function __construct(Helper $helper, PageConfig $pageConfig, CanonicalFactory $canonicalFactory)
    {
        $this->helper           = $helper;
        $this->pageConfig       = $pageConfig;
        $this->canonicalFactory = $canonicalFactory;
    }

    /**
     * @param SocialMarkupCategory $subject
     * @param callable $proceed
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetPreparedUrl(SocialMarkupCategory $subject, callable $proceed): string
    {
        if ($this->helper->isDisableCanonicalByRobots()
            && stripos($this->pageConfig->getRobots(), 'noindex') !== false
        ) {
            return $proceed();
        }

        $canonicalModel = $this->canonicalFactory->get('catalog_category_view');
        $canonicalUrl   = $canonicalModel->getCanonicalUrl();

        if ($canonicalUrl) {
            return $canonicalUrl;
        }

        return $proceed();
    }
}
