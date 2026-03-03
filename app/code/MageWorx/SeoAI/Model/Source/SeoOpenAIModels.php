<?php

namespace MageWorx\SeoAI\Model\Source;

class SeoOpenAIModels extends \MageWorx\OpenAI\Model\Source\OpenAIModels
{
    protected array $allowedModelsForSeo = [];

    /**
     * @param array $allowedModelsForSeo
     */
    public function __construct(
        array $allowedModelsForSeo = []
    ) {
        $this->allowedModelsForSeo = $allowedModelsForSeo;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $origOptions = parent::toOptionArray();
        foreach ($origOptions as $origOption) {
            if (in_array($origOption['value'], $this->allowedModelsForSeo)) {
                $options[] = $origOption;
            }
        }

        return $options;
    }
}
