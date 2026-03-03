<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\Collection as QueueCollection;
use MageWorx\OpenAI\Model\ResourceModel\QueueProcess\CollectionFactory as QueueCollectionFactory;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'MageWorx_OpenAI::delete_process';

    protected Filter $filter;

    protected QueueCollectionFactory $collectionFactory;

    public function __construct(
        Context                $context,
        Filter                 $filter,
        QueueCollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var QueueCollection $collection */
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $size       = $collection->massDelete();

        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $size)
            );
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
