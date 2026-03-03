<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\Process;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\OpenAI\Controller\Adminhtml\QueueItem\MassDenyQueueItemsResult;

class MassDenyItems extends MassDenyQueueItemsResult
{
    const ADMIN_RESOURCE = 'MageWorx_OpenAI::manage_process';

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = parent::execute();

        $processId = $this->detectProcessId();
        if (!$processId) {
            return $resultRedirect;
        }

        $resultRedirect->setPath('*/*/viewProcess', ['id' => $processId, '_current' => true]);

        return $resultRedirect;
    }
}
