<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\Js;

class Information extends Fieldset
{
    /**
     * Anchor is sitemap
     */
    const PATH_ANCHOR_SITEMAP = 'sitemap';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Information constructor.
     *
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $linkText = __('the standard Magento XML sitemap settings');

        $comment = __(
            'NOTE: To specify frequency, priority, file limitations and another settings please use %1.',
            '<a target="_blank" href="' . $this->getConfigSitemapUrl() . '">' . $linkText . '</a>'
        );

        return parent::_getHeaderCommentHtml($element) . '<div class="comment">' . $comment . '</div>';
    }

    /**
     * Get url for config params
     *
     * @return string
     */
    protected function getConfigSitemapUrl()
    {
        return $this->urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            [
                'section' => 'sitemap',
            ]
        );
    }
}
