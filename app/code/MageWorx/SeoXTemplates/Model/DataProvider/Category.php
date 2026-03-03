<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DataProvider;

use Magento\Framework\App\ResourceConnection;
use MageWorx\SeoXTemplates\Model\ConverterCategoryFactory;

abstract class Category extends \MageWorx\SeoXTemplates\Model\DataProvider
{
    /**
     * @var ConverterCategoryFactory
     */
    protected $converterCategoryFactory;

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resource
     * @param ConverterCategoryFactory $converterCategoryFactory
     */
    public function __construct(
        ResourceConnection $resource,
        ConverterCategoryFactory $converterCategoryFactory
    ) {
        parent::__construct($resource);
        $this->converterCategoryFactory = $converterCategoryFactory;
    }
}
