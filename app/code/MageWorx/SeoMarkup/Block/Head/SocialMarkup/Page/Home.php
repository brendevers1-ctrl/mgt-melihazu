<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page;

class Home extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPreparedUrl(): string
    {
        if (in_array(parse_url($this->_urlBuilder->getCurrentUrl()), ['/graphql', '/graphql/']) && $this->getEntity()) {
            return $this->_storeManager->getStore()->getBaseUrl();
        }

        return parent::getPreparedUrl();
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function isOgEnabled()
    {
        return $this->openGraphConfigProvider->isEnabledForHomePage();
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function isTwEnabled()
    {
        return $this->twCardsConfigProvider->isEnabledForHomePage() && $this->twCardsConfigProvider->getUsername();
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getTwImageUrl()
    {
        return '';
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getTwUsername()
    {
        return $this->twCardsConfigProvider->getUsername();
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getOgType()
    {
        return 'website';
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getTwType()
    {
        return 'summary_large_image';
    }
}
