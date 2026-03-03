<?php

namespace MageWorx\SeoAI\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\SeoAI\Api\Data\CategoryRequestResponseLogInterface;
use MageWorx\SeoAI\Api\Data\RequestResponseLogInterface;

class CategoryRequestResponseLog extends AbstractRequestResponseLog
    implements CategoryRequestResponseLogInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'category_request_response_log';

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
        $this->_init(\MageWorx\SeoAI\Model\ResourceModel\CategoryRequestResponseLog::class);
    }

    public function setCategoryId(int $value): CategoryRequestResponseLogInterface
    {
        return $this->setData('category_id', $value);
    }

    public function getCategoryId(): ?int
    {
        return $this->getData('category_id');
    }
}
