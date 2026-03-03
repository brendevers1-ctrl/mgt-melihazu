<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api;

use Magento\Framework\Exception\LocalizedException;

interface GeneralOpenAIHelperInterface
{
    /**
     * Split long request to parts. Each part will be sent to API separately.
     * Part size is limited due to the model max context length (differs by model type).
     *
     * @param string $content
     * @param array $context
     * @param int $contextMaxAllowedLength
     * @return array
     */
    public function splitRequestToParts(
        string $content,
        array  $context,
        int    $contextMaxAllowedLength
    ): array;

    /**
     * @param string $string
     * @return int
     */
    public function calculateStringLengthInTokens(string $string): int;

    /**
     * @param array $context
     * @return int
     */
    public function calculateContextLength(array $context): int;

    /**
     * @param string $model
     * @return int
     * @throws LocalizedException
     */
    public function getMaxModelSupportedContextLength(string $model): int;
}
