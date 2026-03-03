<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\App\Emulation;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use MageWorx\XmlSitemap\Model\Source\EntityType;
use MageWorx\XmlSitemap\Model\Generator\Product as GeneratorProduct;
use MageWorx\XmlSitemap\Model\Spi\SitemapResourceInterface;

/**
 * {@inheritdoc}
 */
class GeneratorManager
{
    /**
     * @var Sitemap
     */
    protected $model;

    /**
     * @var
     */
    protected $helper;

    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var GeneratorFactory
     */
    protected $generatorFactory;

    /**
     * @var GeneratorProduct
     */
    protected $generatorProduct;

    /**
     * @var Writer
     */
    protected $xmlWriter;

    /**
     * @var SitemapResourceInterface
     */
    protected $sitemapResource;

    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var array
     */
    protected $entitiesCounterData;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * GeneratorManager constructor.
     *
     * @param GeneratorFactory $generatorFactory
     * @param Helper $helper
     * @param WriterInterface $xmlWriter
     * @param GeneratorProduct $generatorProduct
     * @param SitemapResourceInterface $sitemapResource
     * @param Emulation $appEmulation
     * @param DateTime $date
     */
    public function __construct(
        GeneratorFactory $generatorFactory,
        Helper $helper,
        WriterInterface $xmlWriter,
        GeneratorProduct $generatorProduct,
        SitemapResourceInterface $sitemapResource,
        Emulation $appEmulation,
        DateTime $date
    ) {
        $this->generatorProduct = $generatorProduct;
        $this->generatorFactory = $generatorFactory;
        $this->helper           = $helper;
        $this->xmlWriter        = $xmlWriter;
        $this->sitemapResource  = $sitemapResource;
        $this->appEmulation     = $appEmulation;
        $this->date             = $date;
    }

    /**
     * @param Sitemap $model
     * @throws LocalizedException
     */
    protected function init(Sitemap $model)
    {
        $this->model   = $model;
        $this->storeId = $model->getStoreId();
        $this->helper->init($this->storeId);

        $this->xmlWriter->init(
            $this->model->getFullPath(),
            $this->model->getPath(),
            $this->model->getSitemapFilename(),
            $this->model->getFullTempPath(),
            $this->model->getStoreBaseUrl(),
            $this->storeId,
            $this->model->getServerPath()
        );
    }

    /**
     * @param Sitemap $sitemap
     * @throws LocalizedException
     */
    public function generateXml(Sitemap $sitemap)
    {
        $this->appEmulation->startEnvironmentEmulation(
            $sitemap->getStoreId(),
            Area::AREA_FRONTEND,
            true
        );

        $this->init($sitemap);
        $this->xmlWriter->startWriteXml();

        $sitemapEntityType = $sitemap->getEntityType();

        foreach ($this->generatorFactory->getAllGenerators() as $generatorCode => $model) {

            if (!$this->isSuitableGenerator($sitemapEntityType, $generatorCode)) {
                continue;
            }

            $this->generator = $model;

            if ($this->generator instanceof MediaGeneratorInterface) {
                $this->generator->setUsePubInMediaUrls($sitemap->hasPubMediaUrl());
            }

            $this->generator->generate($this->storeId, $this->xmlWriter);
            $this->composeEntitiesCounterData($generatorCode);
        }

        $this->xmlWriter->endWriteXml();
        $this->appEmulation->stopEnvironmentEmulation();

        $sitemap->setSitemapTime($this->date->gmtDate());
        $sitemap->setCountByEntity($this->convertEntitiesCounterDataToString());
        $this->resetEntitiesCounterData();
        $this->sitemapResource->save($sitemap);
    }

    /**
     * @return array
     */
    public function getEntitiesCounterData()
    {
        return $this->entitiesCounterData;
    }

    /**
     * Reset the links counter
     */
    protected function resetEntitiesCounterData()
    {
        $this->entitiesCounterData = [];
    }

    /**
     * @param string $sitemapEntityType
     * @param string $generatorCode
     * @return bool
     */
    protected function isSuitableGenerator($sitemapEntityType, $generatorCode): bool
    {
        if ($sitemapEntityType == EntityType::DEFAULT_TYPE) {
            return true;
        }

        if ($sitemapEntityType == $generatorCode) {
            return true;
        }

        if (
            $sitemapEntityType == EntityType::ADDITIONAL_LINK_TYPE
            && $generatorCode == EntityType::GENERATORS_BY_OBSERVER_TYPE
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function convertEntitiesCounterDataToString(): string
    {
        $text = '';
        $data = $this->getEntitiesCounterData();

        foreach ($data as $item) {
            if (!$item['title']) {
                continue;
            }

            $text .= $item['title'];

            if ($item['value'] >= 0) {
                $text .= ' - ' . $item['value'];
            }

            $additionalText     = '';
            $additionalTextList = [];
            foreach ($item as $itemKey => $itemAdditional) {
                if (is_array($itemAdditional)) {
                    $additionalTextList[] = $item[$itemKey]['title'] . ' - ' . $item[$itemKey]['value'];
                }
            }

            if ($additionalTextList) {
                $additionalText = ' (' . implode('; ', $additionalTextList) . ')';
            }

            $text .= $additionalText . ';<br/>';
        }

        return $text;
    }

    /**
     * @param string $generatorCode
     */
    protected function composeEntitiesCounterData($generatorCode)
    {
        $this->entitiesCounterData[$generatorCode] = [
            'title' => $this->generator->getName(),
            'value' => $this->generator->getCounter(),
        ];

        if ($this->generator instanceof MediaGeneratorInterface) {

            if ($this->generator->getIsAllowedImages()) {
                $imageCounter                                                 = $this->generator->getImageCounter();
                $this->entitiesCounterData[$generatorCode]['images']['title'] = __('Images');
                $this->entitiesCounterData[$generatorCode]['images']['value'] = $imageCounter;
            }

            if ($this->generator->getIsAllowedVideo()) {
                $videoCounter                                                = $this->generator->getVideoCounter();
                $this->entitiesCounterData[$generatorCode]['video']['title'] = __('Videos');
                $this->entitiesCounterData[$generatorCode]['video']['value'] = $videoCounter;
            }
        }
    }
}
