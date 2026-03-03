<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class QueueItemPreprocessingException extends LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(
        Phrase $phrase,
        \Exception $cause = null,
        int $code = 1002
    ) {
        parent::__construct($phrase, $cause, $code);
    }
}
