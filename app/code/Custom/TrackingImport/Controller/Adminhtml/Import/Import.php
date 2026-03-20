<?php

namespace Custom\TrackingImport\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Import extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
