<?php

namespace Custom\TrackingImport\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Import extends Template
{
    public function getFormAction()
    {
        return $this->getUrl('trackingimport/import/upload');
    }
}

