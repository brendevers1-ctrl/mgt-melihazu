<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OpenAI\Api\GeneralOpenAIHelperInterface;
use MageWorx\OpenAI\Model\Models\ModelsFactory;

class General extends AbstractHelper implements GeneralOpenAIHelperInterface
{
    const STRING_TO_TOKEN_MULTIPLIER = 3;

    protected Context       $context;
    protected Json          $json;
    protected ModelsFactory $modelsFactory;

    public function __construct(
        Context       $context,
        Json          $json,
        ModelsFactory $modelsFactory
    ) {
        $this->context       = $context;
        $this->json          = $json;
        $this->modelsFactory = $modelsFactory;
    }

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
    ): array {
        $parts          = [];
        $partialContext = [];
        $contentSize    = $this->calculateStringLengthInTokens($content);
        $size           = $contentSize;

        while (!empty($context)) {
            $part     = array_pop($context);
            $partSize = $this->calculateStringLengthInTokens(!is_string($part) ? $this->json->serialize($part) : $part);
            $size     += $partSize;

            if ($size >= $contextMaxAllowedLength) {
                // Size with current part is more than allowed, add part to stack and reset size
                $parts[]          = $partialContext;
                $partialContext   = [];
                $partialContext[] = $part;
                $size             = $contentSize + $partSize;
            } else {
                // Size with this part is still less than allowed
                $partialContext[] = $part;
            }

            if (empty($context)) {
                // Add final part to stack
                $parts[] = $partialContext;
            }
        }

        return $parts;
    }

    /**
     * @param string $string
     * @return int
     */
    public function calculateStringLengthInTokens(string $string): int
    {
        $length = strlen($string);
        $tokens = $length / static::STRING_TO_TOKEN_MULTIPLIER;

        return (int)$tokens;
    }

    /**
     * @param array $context
     * @return int
     */
    public function calculateContextLength(array $context): int
    {
        $contextJson = $this->json->serialize($context);
        if (!$contextJson) {
            return 0;
        }

        return $this->calculateStringLengthInTokens($contextJson);
    }

    /**
     * @param string $model
     * @return int
     * @throws LocalizedException
     */
    public function getMaxModelSupportedContextLength(string $model): int
    {
        $modelEntity = $this->modelsFactory->create($model);

        return $modelEntity->getMaxContextLength();
    }
}
