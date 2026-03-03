<?php
declare(strict_types = 1);

namespace MageWorx\SeoAI\Controller\Adminhtml\Product\Logs;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const MENU_ID = 'MageWorx_SeoAI::request_logs';

    /**
     * Initiate action
     *
     * @return Index
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(static::MENU_ID)
             ->_addBreadcrumb(
                 __('SEO AI Request Logs for Products'),
                 __('SEO AI Request Logs for Products')
             );

        return $this;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('SEO AI Request Logs for Products'));

        return $resultPage;
    }
}
