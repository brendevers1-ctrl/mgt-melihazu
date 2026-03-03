<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ProcessActions extends Column
{
    protected UrlInterface $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit']       = [
                    'href'      => $this->urlBuilder->getUrl(
                        'mageworx_openai/process/viewProcess',
                        ['id' => $item['entity_id']]
                    ),
                    'ariaLabel' => __('Manage ') . $item['name'],
                    'label'     => __('Manage'),
                    'hidden'    => false,
                ];
                $item[$this->getData('name')]['quick_view'] = [
                    'type'     => 'mageworx_open_ai_process_quick_view',
                    'label'    => __('Quick View'),
                    'hidden'   => false,
                    'callback' => [
                        [
                            'target'   => 'toggleModal',
                            'provider' => 'mageworx_openai_process_listing.mageworx_openai_process_listing.process_view_modal'
                        ],
                        [
                            'provider' => 'mageworx_openai_process_listing.mageworx_openai_process_listing.process_view_modal.general.process_id',
                            'target'   => 'value',
                            'params'   => $item['entity_id']
                        ],
                        [
                            'provider' => 'mageworx_openai_process_listing.mageworx_openai_process_listing.process_view_modal',
                            'target'   => 'setTitle',
                            'params'   => $item['name'] . ' (ID: ' . $item['entity_id'] . ')'
                        ]
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
