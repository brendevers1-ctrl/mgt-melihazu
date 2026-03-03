<?php

namespace MageWorx\OpenAI\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OpenAIModels implements OptionSourceInterface
{
    // Add OpenAI models here
    private const MODELS = [
        "babbage",
        "davinci",
        "text-davinci-edit-001",
        "babbage-code-search-code",
        "text-similarity-babbage-001",
        "code-davinci-edit-001",
        "ada",
        "babbage-code-search-text",
        "babbage-similarity",
        "gpt-3.5-turbo-16k-0613",
        "code-search-babbage-text-001",
        "text-curie-001",
        "gpt-3.5-turbo-0301",
        "gpt-3.5-turbo-16k",
        "code-search-babbage-code-001",
        "text-ada-001",
        "text-davinci-003",
        "text-similarity-ada-001",
        "text-davinci-002",
        "curie-instruct-beta",
        "ada-code-search-code",
        "ada-similarity",
        "code-search-ada-text-001",
        "text-search-ada-query-001",
        "davinci-search-document",
        "whisper-1",
        "ada-code-search-text",
        "text-search-ada-doc-001",
        "davinci-instruct-beta",
        "text-similarity-curie-001",
        "code-search-ada-code-001",
        "ada-search-query",
        "text-search-davinci-query-001",
        "curie-search-query",
        "davinci-search-query",
        "babbage-search-document",
        "ada-search-document",
        "text-search-curie-query-001",
        "text-babbage-001",
        "text-search-babbage-doc-001",
        "curie-search-document",
        "text-search-curie-doc-001",
        "babbage-search-query",
        "text-search-davinci-doc-001",
        "text-search-babbage-query-001",
        "curie-similarity",
        "text-davinci-001",
        "text-embedding-ada-002",
        "curie",
        "text-similarity-davinci-001",
        "gpt-3.5-turbo-0613",
        "davinci-similarity",
        "gpt-3.5-turbo",
        "gpt-4",
        "gpt-4-1106-preview",
        "gpt-4o",
    ];

    public function toOptionArray(): array
    {
        $options = [];
        foreach (self::MODELS as $value) {
            $label = $this->getLabel($value);
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }

    private function getLabel(string $model): string
    {
        return $model;
    }
}
