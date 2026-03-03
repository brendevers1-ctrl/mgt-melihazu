<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\Generators\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Model\Models\ModelsFactory;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterfaceFactory as LogFactory;
use MageWorx\SeoAI\Api\GeneratorInterface;
use MageWorx\SeoAI\Api\ProductRequestResponseLogRepositoryInterface as LogRepository;
use MageWorx\SeoAI\Helper\Config as ModuleConfig;
use MageWorx\SeoAI\Model\Source\SeoGenerationStatuses;

class ImproveText extends AbstractProductGenerator implements GeneratorInterface
{
    protected string $label;

    public function __construct(
        ModelsFactory                   $modelsFactory,
        OptionsInterfaceFactory         $optionsFactory,
        RequestInterfaceFactory         $requestFactory,
        ProductRepositoryInterface      $productRepository,
        CategoryRepositoryInterface     $categoryRepository,
        LogFactory                      $logFactory,
        LogRepository                   $logRepository,
        PricingHelper                   $pricingHelper,
        ModuleConfig                    $moduleConfig,
        StoreManagerInterface           $storeManager,
        QueueManagementInterface        $queueManagement,
        QueueProcessManagementInterface $queueProcessManagement,
        SeoGenerationStatuses           $seoGenerationStatuses,
        string                          $type = '',
        string                          $label = ''
    ) {
        if (!$type) {
            throw new \InvalidArgumentException('Type is not specified.');
        }
        parent::__construct(
            $modelsFactory,
            $optionsFactory,
            $requestFactory,
            $productRepository,
            $categoryRepository,
            $logFactory,
            $logRepository,
            $pricingHelper,
            $moduleConfig,
            $storeManager,
            $queueManagement,
            $queueProcessManagement,
            $seoGenerationStatuses
        );
        $this->type  = $type;
        $this->label = $label;
    }
}
