<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Observer;

use Magento\Framework\View\Page\Config;
use MageWorx\SeoBase\Model\RobotsFactory;
use MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider;

/**
 * Observer class for robots
 */
class Robots implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageWorx\SeoBase\Model\RobotsFactory
     */
    protected $robotsFactory;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var $moduleName
     */
    protected $moduleName = 'SeoBase';

    /**
     * @var \MageWorx\SeoAll\Helper\SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    protected $seoFeaturesStatusProvider;

    /**
     * Robots constructor.
     *
     * @param Config $pageConfig
     * @param RobotsFactory $robotsFactory
     * @param SeoFeaturesStatusProvider $seoFeaturesStatusProvider
     */
    public function __construct(
        Config $pageConfig,
        RobotsFactory $robotsFactory,
        SeoFeaturesStatusProvider $seoFeaturesStatusProvider
    ) {

        $this->pageConfig                = $pageConfig;
        $this->robotsFactory             = $robotsFactory;
        $this->seoFeaturesStatusProvider = $seoFeaturesStatusProvider;
    }

    /**
     * Set robots to page config
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->seoFeaturesStatusProvider->getStatus($this->moduleName)) {
            return;
        }

        //layout_generate_blocks_before
        $fullActionName = $observer->getFullActionName();
        $arguments      = ['layout' => $observer->getLayout(), 'fullActionName' => $fullActionName];
        $robotsInstance = $this->robotsFactory->create($fullActionName, $arguments);
        $robots         = $robotsInstance->getRobots();

        if ($robots) {
            $this->pageConfig->setRobots($robots);
        }
    }
}
