<?php

namespace Custom\OrderToSheet\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    protected $fileName = '/var/log/order_to_sheet.log';
    protected $loggerType = Logger::DEBUG;
}
