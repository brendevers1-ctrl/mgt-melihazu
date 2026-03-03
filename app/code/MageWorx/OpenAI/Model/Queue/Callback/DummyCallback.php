<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Queue\Callback;

use MageWorx\OpenAI\Api\CallbackInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\ResponseInterface;
use MageWorx\OpenAI\Exception\CallbackProcessingException;

class DummyCallback implements CallbackInterface
{
    public function execute(
        OptionsInterface  $options,
        ResponseInterface $response,
        ?array            $additionalData = []
    ): void {
        throw new CallbackProcessingException(__('Callback is not implemented'));
    }
}
