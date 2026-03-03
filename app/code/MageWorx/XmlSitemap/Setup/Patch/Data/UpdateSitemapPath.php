<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\Collection;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Psr\Log\LoggerInterface;

class UpdateSitemapPath implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var CollectionFactory
     */
    protected $sitemapCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory        $sitemapCollectionFactory,
        LoggerInterface          $logger
    ) {
        $this->moduleDataSetup          = $moduleDataSetup;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->logger                   = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        try {
            /** @var Collection $sitemapCollection */
            $sitemapCollection = $this->sitemapCollectionFactory->create();

            //convert paths on model saving
            $sitemapCollection->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            return $this;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return '2.0.8';
    }
}
