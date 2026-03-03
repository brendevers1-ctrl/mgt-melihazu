<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block;

abstract class Head extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $entity;

    /**
     * @return string (HTML)
     */
    abstract protected function getMarkupHtml();

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity): Head
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
