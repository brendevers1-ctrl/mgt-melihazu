<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model\Generators\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\QueueManagementInterface;
use MageWorx\OpenAI\Api\QueueProcessManagementInterface;
use MageWorx\OpenAI\Api\RequestInterfaceFactory;
use MageWorx\OpenAI\Model\Models\ModelsFactory;
use MageWorx\SeoAI\Api\CategoryRequestResponseLogRepositoryInterface as LogRepository;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterfaceFactory as LogFactory;
use MageWorx\SeoAI\Api\GeneratorInterface;
use MageWorx\SeoAI\Helper\Config as ModuleConfig;
use MageWorx\SeoAI\Model\Source\SeoGenerationStatuses;

class ImproveText extends AbstractCategoryGenerator implements GeneratorInterface
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
        ModuleConfig                    $moduleConfig,
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
            $moduleConfig,
            $queueManagement,
            $queueProcessManagement,
            $seoGenerationStatuses
        );
        $this->type  = $type;
        $this->label = $label;
    }
}
