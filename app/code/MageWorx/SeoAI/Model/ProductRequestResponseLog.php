<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\SeoAI\Api\Data\ProductRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\RequestResponseLogInterface;

class ProductRequestResponseLog extends AbstractRequestResponseLog
    implements ProductRequestResponseLogInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'product_request_response_log';

    /**
     * @var string
     */
    protected $_eventObject = 'log';

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog::class);
    }

    public function setProductId(int $value): ProductRequestResponseLogInterface
    {
        return $this->setData('product_id', $value);
    }

    public function getProductId(): ?int
    {
        return $this->getData('product_id');
    }
}
