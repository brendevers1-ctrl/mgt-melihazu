<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DataProvider;

use Magento\Framework\App\ResourceConnection;
use MageWorx\SeoXTemplates\Model\ConverterProductFactory;

abstract class Product extends \MageWorx\SeoXTemplates\Model\DataProvider
{
    /**
     * @var ConverterProductFactory
     */
    protected $converterProductFactory;

    /**
     * Product constructor.
     *
     * @param ResourceConnection $resource
     * @param ConverterProductFactory $converterProductFactory
     */
    public function __construct(
        ResourceConnection $resource,
        ConverterProductFactory $converterProductFactory
    ) {
        parent::__construct($resource);
        $this->converterProductFactory = $converterProductFactory;
    }
}
