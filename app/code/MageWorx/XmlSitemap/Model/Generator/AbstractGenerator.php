<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use Magento\Framework\ObjectManagerInterface;
use MageWorx\XmlSitemap\Helper\Data as Helper;
use MageWorx\XmlSitemap\Model\GeneratorInterface;
use MageWorx\XmlSitemap\Model\Sitemap;

/**
 * {@inheritdoc}
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    const COLLECTION_LIMIT = 500;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $storeBaseUrl;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * AbstractGenerator constructor.
     *
     * @param Helper $helper
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Helper $helper,
        ObjectManagerInterface $objectManager
    ) {
        $this->helper        = $helper;
        $this->objectManager = $objectManager;
    }

    /**
     * Return count of urls
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Return generator code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return generator code
     *
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Sitemap $model
     * @return string
     */
    protected function getItemChangeDate($model)
    {
        $upTime = $model->getUpdatedAt();
        if ($upTime == '0000-00-00 00:00:00') {
            $upTime = $model->getCreatedAt();
        }
        
        $timestamp = $upTime ? strtotime($upTime) : null;
        if (!$timestamp) {
            return $this->helper->getCurrentDate();
        }

        return date('c', $timestamp);
    }
}
