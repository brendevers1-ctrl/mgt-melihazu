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

class UseCanonicalUrlInHomePageSocialMarkupPlugin
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
     * UseCanonicalUrlInHomePageSocialMarkupPlugin constructor.
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
     * @param \MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page\Home $subject
     * @param callable $proceed
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetPreparedUrl(
        \MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page\Home $subject,
        callable $proceed
    ): string {
        if ($this->helper->isDisableCanonicalByRobots()
            && stripos($this->pageConfig->getRobots(), 'noindex') !== false
        ) {
            return $proceed();
        }

        $canonicalModel = $this->canonicalFactory->get('cms_index_index');
        $canonicalUrl   = $canonicalModel->getCanonicalUrl();

        if ($canonicalUrl) {
            return $canonicalUrl;
        }

        return $proceed();
    }
}
