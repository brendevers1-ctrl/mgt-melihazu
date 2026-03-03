<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoXTemplates\Ui\Component\Listing\Column;

use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoAll\Helper\MagentoVersion;

abstract class TemplateActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MagentoVersion
     */
    protected $helperVersion;

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getApplyUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getTestApplyUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getEditUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getDeleteUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getDeleteMessage();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getApplyMessage();

    /**
     * TemplateActions constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param MagentoVersion $helperVersion
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        MagentoVersion $helperVersion,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager  = $storeManager;
        $this->urlBuilder    = $urlBuilder;
        $this->helperVersion = $helperVersion;
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
            $confirmDelete = [
                'title'   => __('Delete "${ $.$data.name }"'),
                'message' => $this->getDeleteMessage()
            ];
            $confirmApply  = [
                'title'   => __('Apply "${ $.$data.name }"'),
                'message' => $this->getApplyMessage()
            ];

            if ($this->helperVersion->checkModuleVersion('Magento_Ui', '101.1.4')) {
                $confirmDelete['__disableTmpl'] = ['title' => false, 'message' => false];
                $confirmApply['__disableTmpl']  = ['title' => false, 'message' => false];
            }

            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['template_id'])) {

                    if (isset($item['is_single_store_mode'])
                        && ($this->storeManager->isSingleStoreMode() != $item['is_single_store_mode'])
                    ) {
                        $item[$this->getData('name')] = [
                            'edit'   => [
                                'href'  => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label' => __('Edit')
                            ],
                            'delete' => [
                                'href'    => $this->urlBuilder->getUrl(
                                    static::URL_PATH_DELETE,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label'   => __('Delete'),
                                'confirm' => $confirmDelete
                            ]
                        ];
                    } else {
                        $item[$this->getData('name')] = [
                            'test_apply' => [
                                'href'  => $this->urlBuilder->getUrl(
                                    static::URL_PATH_TEST_APPLY,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label' => __('Test Apply')
                            ],
                            'apply'      => [
                                'href'    => $this->urlBuilder->getUrl(
                                    static::URL_PATH_APPLY,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label'   => __('Apply'),
                                'confirm' => $confirmApply
                            ],
                            'edit'       => [
                                'href'  => $this->urlBuilder->getUrl(
                                    static::URL_PATH_EDIT,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label' => __('Edit')
                            ],
                            'delete'     => [
                                'href'    => $this->urlBuilder->getUrl(
                                    static::URL_PATH_DELETE,
                                    [
                                        'template_id' => $item['template_id']
                                    ]
                                ),
                                'label'   => __('Delete'),
                                'confirm' => $confirmDelete
                            ]
                        ];
                    }

                }
            }
        }

        return $dataSource;
    }
}
