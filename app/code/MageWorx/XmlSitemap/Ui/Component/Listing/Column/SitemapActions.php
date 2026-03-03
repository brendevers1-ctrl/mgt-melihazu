<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\XmlSitemap\Ui\Component\Listing\Column;

use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MageWorx\SeoAll\Helper\MagentoVersion;

class SitemapActions extends Column
{
    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'mageworx_xmlsitemap/sitemap/delete';

    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'mageworx_xmlsitemap/sitemap/edit';

    /**
     * Url path to apply
     *
     * @var string
     */
    const URL_PATH_GENERATE = 'mageworx_xmlsitemap/sitemap/generate';

    /**
     * @var MagentoVersion
     */
    protected $helperVersion;

    /**
     * SitemapActions constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param MagentoVersion $helperVersion
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MagentoVersion $helperVersion,
        array $components = [],
        array $data = []
    ) {
        $this->helperVersion = $helperVersion;
        $this->urlBuilder    = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $confirm = [
                'title'   => __('Delete "${ $.$data.sitemap_filename}"'),
                'message' => $this->getDeleteMessage(),
            ];

            if ($this->helperVersion->checkModuleVersion('Magento_Ui', '101.1.4')) {
                $confirm['__disableTmpl'] = ['title' => false];
            }

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['sitemap_id'])) {
                    $item[$this->getData('name')] = [
                        'generate' => [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_GENERATE,
                                [
                                    'sitemap_id' => $item['sitemap_id']
                                ]
                            ),
                            'label' => __('Generate')
                        ],
                        'edit'     => [
                            'href'  => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'sitemap_id' => $item['sitemap_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete'   => [
                            'href'    => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'sitemap_id' => $item['sitemap_id']
                                ]
                            ),
                            'label'   => __('Delete'),
                            'confirm' => $confirm
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @return Phrase|string
     */
    protected function getDeleteMessage()
    {
        return __('Are you sure you want to delete sitemap?');
    }
}
