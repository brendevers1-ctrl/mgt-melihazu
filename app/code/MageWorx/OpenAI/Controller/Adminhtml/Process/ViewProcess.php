<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use MageWorx\OpenAI\Api\Data\QueueProcessInterface;
use MageWorx\OpenAI\Api\QueueProcessRepositoryInterface;
use MageWorx\OpenAI\Model\Queue\QueueProcess;

class ViewProcess extends Action implements HttpGetActionInterface
{
    const MENU_ID = 'MageWorx_OpenAI::generate_process';
    protected QueueProcessRepositoryInterface $queueProcessRepository;

    public function __construct(
        Action\Context                  $context,
        QueueProcessRepositoryInterface $queueProcessRepository
    ) {
        parent::__construct($context);
        $this->queueProcessRepository = $queueProcessRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu(static::MENU_ID);

        if ($this->getRequest()->getParam('id')) {
            $processId = (int)$this->getRequest()->getParam('id');
            /** @var QueueProcessInterface|QueueProcess */
            $process = $this->queueProcessRepository->getById($processId);
            if (!$process->getId()) {
                throw new NotFoundException(__('Process with id "%1" does not exist.', $processId));
            }
            $resultPage->getConfig()
                       ->getTitle()
                       ->prepend(__('%1 (ID: %2)', $process->getName(), $process->getId()));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Generated Items'));
        }

        return $resultPage;
    }
}
