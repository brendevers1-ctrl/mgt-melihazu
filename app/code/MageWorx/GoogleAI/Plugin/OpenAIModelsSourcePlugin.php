<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\GoogleAI\Plugin;

/**
 * Add gemini models to the source list (option array))
 */
class OpenAIModelsSourcePlugin
{
    /**
     * @param \MageWorx\OpenAI\Model\Source\OpenAIModels $subject
     * @param array $result
     * @return array
     */
    public function afterToOptionArray(\MageWorx\OpenAI\Model\Source\OpenAIModels $subject, array $result): array
    {
        $result[] = [
            'value' => 'gemini-1.5-pro',
            'label' => __('Gemini 1.5 Pro')
        ];
        $result[] = [
            'value' => 'gemini-1.5-flash',
            'label' => __('Gemini 1.5 Flash')
        ];

        return $result;
    }
}
