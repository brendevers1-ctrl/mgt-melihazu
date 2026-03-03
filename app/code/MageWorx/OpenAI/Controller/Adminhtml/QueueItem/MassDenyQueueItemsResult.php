<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\QueueItem;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\Collection as Collection;

class MassDenyQueueItemsResult extends AbstractQueueItemMassAction
{
    const ADMIN_RESOURCE = 'MageWorx_OpenAI::manage_process';

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Collection $collection */
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );

        $collection->addFieldToFilter('status', ['in' => $this->getAllowedStatusesForCallback()]);

        $size = $collection->deny();

        $this->messageManager
            ->addSuccessMessage(
                __('A total of %1 records were denied. Items with inappropriate status were ignored.', $size)
            );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
