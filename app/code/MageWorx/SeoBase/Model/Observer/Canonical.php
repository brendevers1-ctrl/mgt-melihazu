<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer;

use Magento\Framework\View\Page\Config;
use MageWorx\SeoBase\Model\CanonicalFactory as CanonicalFactory;
use MageWorx\SeoBase\Helper\Data as HelperData;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

/**
 * Observer class for canonical URL
 */
class Canonical implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\SeoBase\Model\CanonicalFactory
     */
    protected $canonicalFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \MageWorx\SeoBase\Helper\Data
     */
    protected $helperData;

    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoBase';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * Canonical constructor.
     *
     * @param Config $pageConfig
     * @param CanonicalFactory $canonicalFactory
     * @param HelperData $helperData
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        Config $pageConfig,
        CanonicalFactory $canonicalFactory,
        HelperData $helperData,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {
        $this->pageConfig                = $pageConfig;
        $this->canonicalFactory          = $canonicalFactory;
        $this->helperData                = $helperData;
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
    }

    /**
     * Set canonical URL to page config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
            return;
        }

        if ($this->helperData->isDisableCanonicalByRobots()
            && stripos($this->pageConfig->getRobots(), 'noindex') !== false
        ) {
            $this->pageConfig->getAssetCollection()->remove('canonical');
            return;
        }

        $fullActionName = $observer->getFullActionName();
        $arguments      = ['layout' => $observer->getLayout(), 'fullActionName' => $fullActionName];
        $canonicalModel = $this->canonicalFactory->create($fullActionName, $arguments);
        $canonicalUrl   = $canonicalModel->getCanonicalUrl();

        if ($canonicalUrl) {
            $this->pageConfig->addRemotePageAsset(
                $canonicalUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
    }
}
