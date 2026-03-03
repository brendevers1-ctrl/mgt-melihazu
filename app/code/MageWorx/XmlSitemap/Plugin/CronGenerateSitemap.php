<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Plugin;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sitemap\Model\Observer;

/**
 * {@inheritdoc}
 */
class CronGenerateSitemap
{
    /** @var EventManagerInterface */
    protected $eventManager;

    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function aroundScheduledGenerateSitemaps(Observer $subject, callable $proceed)
    {
        $this->eventManager->dispatch('mageworx_xmlsitemap_sitemap_generate');
    }
}