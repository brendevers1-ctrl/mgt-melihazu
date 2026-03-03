<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use MageWorx\OpenAI\Exception\CallbackProcessingException;

interface CallbackInterface
{
    /**
     * @param OptionsInterface $options
     * @param ResponseInterface $response
     * @param array|null $additionalData
     * @return void
     * @throws CallbackProcessingException
     */
    public function execute(
        OptionsInterface  $options,
        ResponseInterface $response,
        ?array            $additionalData = []
    ): void;
}
