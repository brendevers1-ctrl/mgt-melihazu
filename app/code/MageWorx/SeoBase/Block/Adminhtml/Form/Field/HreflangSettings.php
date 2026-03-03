<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\SeoBase\Model\HreflangsConfigReader;

class HreflangSettings extends AbstractFieldArray
{
    /**
     * @var StoreView
     */
    protected $storeViewRenderer;

    /**
     * @var LanguageCode
     */
    protected $languageCodeRenderer;

    /**
     * @var CountryCode
     */
    private $countryCodeRenderer;

    /**
     * @var Pages
     */
    protected $pagesRenderer;

    /**
     * @var XDefault
     */
    protected $xDefaultRenderer;

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td colspan="3"><div class="hreflang-settings">';
        $html .= '<div class="label"><label for="' . $element->getHtmlId() . '"><span'
            . $this->_renderScopeLabel($element) . '>' . $element->getLabel() . '</span></label></div>';
        $html .= '<div class="value">' . $this->_getElementHtml($element) . '</div>';
        $html .= '</div></td>';

        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            HreflangsConfigReader::STORE,
            [
                'label'    => __('Store View'),
                'renderer' => $this->getStoreViewRenderer()
            ]
        );
        $this->addColumn(
            HreflangsConfigReader::LANGUAGE_CODE,
            [
                'label'    => __('Language Code'),
                'renderer' => $this->getLanguageCodeRenderer()
            ]
        );
        $this->addColumn(
            HreflangsConfigReader::COUNTRY_CODE,
            [
                'label'    => __('Country Code'),
                'renderer' => $this->getCountryCodeRenderer()
            ]
        );
        $this->addColumn(
            HreflangsConfigReader::PAGES,
            [
                'label'    => __('Pages'),
                'renderer' => $this->getPagesRenderer()
            ]
        );
        $this->addColumn(
            HreflangsConfigReader::X_DEFAULT,
            [
                'label'    => __('X-default'),
                'renderer' => $this->getXDefaultRenderer()
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $store = $row->getStore();
        if ($store !== null) {
            $options['option_' . $this->getStoreViewRenderer()->calcOptionHash($store)] = 'selected="selected"';
        }

        $languageCode = $row->getLanguageCode();
        if ($languageCode !== null) {
            $options['option_' . $this->getLanguageCodeRenderer()->calcOptionHash(
                $languageCode
            )] = 'selected="selected"';
        }

        $countryCode = $row->getCountryCode();
        if ($countryCode !== null) {
            $options['option_' . $this->getCountryCodeRenderer()->calcOptionHash($countryCode)] = 'selected="selected"';
        }

        $pages = $row->getPages();
        if (!empty($pages) && is_array($pages)) {

            foreach ($pages as $page) {
                $options['option_' . $this->getPagesRenderer()->calcOptionHash($page)] = 'selected="selected"';
            }
        }

        $xDefault = $row->getXDefault();
        if ($xDefault !== null) {
            $options['option_' . $this->getXDefaultRenderer()->calcOptionHash($xDefault)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return StoreView
     * @throws LocalizedException
     */
    protected function getStoreViewRenderer()
    {
        if (!$this->storeViewRenderer) {
            $this->storeViewRenderer = $this->getLayout()->createBlock(
                StoreView::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->storeViewRenderer;
    }

    /**
     * @return LanguageCode
     * @throws LocalizedException
     */
    protected function getLanguageCodeRenderer()
    {
        if (!$this->languageCodeRenderer) {
            $this->languageCodeRenderer = $this->getLayout()->createBlock(
                LanguageCode::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->languageCodeRenderer;
    }

    /**
     * @return CountryCode
     * @throws LocalizedException
     */
    protected function getCountryCodeRenderer()
    {
        if (!$this->countryCodeRenderer) {
            $this->countryCodeRenderer = $this->getLayout()->createBlock(
                CountryCode::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->countryCodeRenderer;
    }

    /**
     * @return Pages
     * @throws LocalizedException
     */
    protected function getPagesRenderer()
    {
        if (!$this->pagesRenderer) {
            $this->pagesRenderer = $this->getLayout()->createBlock(
                Pages::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->pagesRenderer;
    }

    /**
     * @return XDefault
     * @throws LocalizedException
     */
    protected function getXDefaultRenderer()
    {
        if (!$this->xDefaultRenderer) {
            $this->xDefaultRenderer = $this->getLayout()->createBlock(
                XDefault::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->xDefaultRenderer;
    }
}
