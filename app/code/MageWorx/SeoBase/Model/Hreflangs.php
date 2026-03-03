<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model;

use MageWorx\SeoBase\Helper\Url as HelperUrl;

abstract class Hreflangs implements \MageWorx\SeoBase\Model\HreflangsInterface
{
    /**
     * @var HreflangsConfigReader
     */
    protected $hreflangsConfigReader;

    /**
     * @var \MageWorx\SeoBase\Helper\Url
     */
    protected $helperUrl;

    /**
     * @var string
     */
    protected $fullActionName;

    /**
     * @var \Magento\Framework\Model\AbstractModel|null
     */
    protected $entity;

    /**
     * Retrieve hreflang URL list:
     * [
     *      (int)$storeId => (string)$hreflangUrl,
     *      ...
     * ]
     *
     * @return array
     */
    abstract public function getHreflangUrls();

    /**
     * Hreflangs constructor.
     *
     * @param \MageWorx\SeoBase\Model\HreflangsConfigReader $hreflangsConfigReader
     * @param HelperUrl $helperUrl
     * @param string $fullActionName
     */
    public function __construct(
        HreflangsConfigReader $hreflangsConfigReader,
        HelperUrl $helperUrl,
        $fullActionName
    ) {
        $this->hreflangsConfigReader = $hreflangsConfigReader;
        $this->helperUrl             = $helperUrl;
        $this->fullActionName        = $fullActionName;
    }

    /**
     * Check if cancel adding hreflangs URL by config setting
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isCancelHreflangs(): bool
    {
        return !$this->hreflangsConfigReader->isHreflangsEnabled();
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity): Hreflangs
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

    /**
     * @param $urlProvider
     * @return bool
     */
    protected function isGraphQl($urlProvider)
    {
        return in_array(
            parse_url($urlProvider->getCurrentUrl(), PHP_URL_PATH),
            ['/graphql', '/graphql/']
        );
    }
}
