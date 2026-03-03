<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page;

class DefaultPage extends \MageWorx\SeoMarkup\Block\Head\SocialMarkup\Page
{
    /**
     * @return string
     */
    public function getPreparedUrl(): string
    {
        if (in_array(
                parse_url($this->_urlBuilder->getCurrentUrl(), PHP_URL_PATH),
                ['/graphql', '/graphql/']
            ) && $this->getEntity()
        ) {
            return $this->_urlBuilder->getUrl(null, ['_direct' => $this->getEntity()->getIdentifier()]);
        }

        return parent::getPreparedUrl();
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
    protected function isOgEnabled()
    {
        return $this->openGraphConfigProvider->isEnabledForPage();
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function isTwEnabled()
    {
        return $this->twCardsConfigProvider->isEnabledForPage() && $this->twCardsConfigProvider->getUsername();
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
        return 'article';
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getTwType()
    {
        return 'summary';
    }
}
