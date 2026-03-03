<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Helper;

/**
 * SEO LinkFieldResolver helper
 */
class LinkFieldResolver extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * LinkFieldResolver constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context);
    }

    /**
     * @param string $class
     * @param string $field - deprecated
     * @return string
     */
    public function getLinkField($class, $field)
    {
        return $this->metadataPool->getMetadata($class)->getLinkField();
    }
}
