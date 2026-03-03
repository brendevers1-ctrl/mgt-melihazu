<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\Component\Listing\MassActions;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ApproveAction extends \Magento\Ui\Component\Action
{
    protected UrlInterface     $urlBuilder;
    protected RequestInterface $request;

    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        UrlInterface     $urlBuilder,
        array            $components = [],
        array            $data = [],
                         $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);

        $this->urlBuilder = $urlBuilder;
        $this->request    = $request;
    }

    /**
     * Add process_id from request to the mass action path as param.
     * It is needed to filter sub-collection of queue items based on currently active process.
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        // Prepare data
        $config  = $this->getConfiguration();
        $urlPath = 'mageworx_openai/process/massApproveItems';
        $params  = ['process_id' => $this->request->getParam('id')];

        // Set url path for mass action with process_id param
        $config['url'] = $this->urlBuilder->getUrl($urlPath, $params);

        $this->setData('config', $config);
    }
}
