<?php

namespace Custom\TrackingImport\Controller\Adminhtml\Import;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Custom_TrackingImport::import';

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Tracking Import'));
        $this->_view->renderLayout();
    }
    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
