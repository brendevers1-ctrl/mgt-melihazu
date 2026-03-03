<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Model\Generator\AbstractGenerator;
use Magento\Framework\DataObject;

/**
 * {@inheritdoc}
 */
class Generator extends AbstractGenerator
{
    /** @var EventManagerInterface */
    protected $eventManager;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * Generator constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager,
        EventManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($helper, $objectManager);
    }

    /**
     * @param int $storeId
     * @param Generator $writer
     */
    public function generate($storeId, $writer, $usePubInMediaUrls = null)
    {
        $this->storeId = $storeId;
        $this->helper->init($this->storeId);
        $this->storeBaseUrl = $writer->storeBaseUrl;
        $container          = new DataObject();
        $container->setGenerators([]);
        $eventArgs = [
            'storeId'             => $storeId,
            'container'           => $container,
            'exclude_meta_robots' => $this->helper->getMetaRobotsExclusion()
        ];

        $this->eventManager->dispatch(
            'mageworx_xmlsitemap_add_generator',
            $eventArgs
        );

        $container = $eventArgs['container'];
        foreach ($container->getGenerators() as $generatorName => $generatorData) {

            if (empty($generatorData['items'])) {
                continue;
            }

            $this->code = $generatorName;
            $priority   = empty($generatorData['priority']) ?
                $this->helper->getPagePriority($storeId) : $generatorData['priority'];
            $changefreq = empty($generatorData['changefreq']) ?
                $this->helper->getPageChangefreq($storeId) : $generatorData['changefreq'];

            foreach ($generatorData['items'] as $item) {
                if (empty($item['url_key'])) {
                    continue;
                }
                $this->counter++;
                $urlKey = $this->getItemUrl($item['url_key']);

                $dateChanged = empty($item['date_changed']) ?
                    $this->helper->getCurrentDate() : $item['date_changed'];

                $writer->write(
                    $urlKey,
                    $dateChanged,
                    $changefreq,
                    $priority
                );
            }

            $this->name    .= empty($generatorData['title']) ? ''
                : $generatorData['title'] . ' - ' . $this->counter . '; ';
            $this->counter = 0;

            unset($generatorData['items']);
        }

        $this->counter = -1;
    }

    /**
     * @param string $urlKey
     * @return string
     */
    protected function getItemUrl($urlKey)
    {
        if (strpos($urlKey, $this->storeBaseUrl) === false) {
            $urlKey = $this->storeBaseUrl . $urlKey;
        }

        return $this->helper->trailingSlash($urlKey);
    }
}