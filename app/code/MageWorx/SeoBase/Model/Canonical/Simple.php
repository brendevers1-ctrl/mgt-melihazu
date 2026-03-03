<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Canonical;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoBase\Api\CustomCanonicalRepositoryInterface;

/**
 * SEO Base non-specific pages canonical URL model
 */
class Simple extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var UrlInterface
     */
    protected UrlInterface          $url;
    protected StoreManagerInterface $storeManager;

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param CustomCanonicalRepositoryInterface $customCanonicalRepository
     * @param UrlInterface $url
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data      $helperData,
        \MageWorx\SeoBase\Helper\Url       $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl  $helperStoreUrl,
        CustomCanonicalRepositoryInterface $customCanonicalRepository,
        UrlInterface                       $url,
        StoreManagerInterface              $storeManager,
                                           $fullActionName = ''
    ) {
        $this->url          = $url;
        $this->storeManager = $storeManager;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $customCanonicalRepository, $fullActionName);
    }

    /**
     * Retrieve non-specific pages canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl(): ?string
    {
        if ($this->isCancelCanonical()) {
            return null;
        }
        try {
            $currentUrl = $this->storeManager->getStore()->getCurrentUrl();
        } catch (\Exception $e) {
            $currentUrl = $this->url->getCurrentUrl();
        } finally {
            if (empty($currentUrl)) {
                $currentUrl = $this->url->getCurrentUrl();
            }
        }

        $url = $this->helperUrl->deleteAllParametrsFromUrl($currentUrl);

        return $this->renderUrl($url);
    }
}
