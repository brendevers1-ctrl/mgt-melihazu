<?php

namespace MageWorx\OpenAI\Model;

use Magento\Framework\Exception\InputException;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Helper\Data as Helper;

class Options implements \MageWorx\OpenAI\Api\OptionsInterface
{
    /**
     * @var array
     */
    protected array  $headers = [];
    protected string $model   = 'gpt-3.5-turbo';
    protected string $path    = '';


    protected float $temperature = 1;
    protected int   $maxTokens   = 128;

    protected int $numberOfResultOptions = 3;

    protected string $httpMethod = self::HTTP_METHOD_POST;
    protected ?string $customKey = null;

    protected Helper $helper;

    /**
     * Initialize default parameters.
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;

        $this->initDefault();
    }

    /**
     * Get current headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeadersSafe(): array
    {
        $headers = $this->getHeaders();
        $result  = [];
        foreach ($headers as $key => $value) {
            if (in_array($key, static::UNSAFE_HEADERS)) {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * Set new headers (remove existing)
     *
     * @param array $headers
     * @return OptionsInterface
     */
    public function setHeaders(array $headers): OptionsInterface
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Add headers to existing headers list
     *
     * @param array $headers
     * @return OptionsInterface
     */
    public function addHeaders(array $headers): OptionsInterface
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Get selected model
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Set model to use
     *
     * @param string $model
     * @return OptionsInterface
     */
    public function setModel(string $model): OptionsInterface
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Initialize default options
     *
     * @return OptionsInterface
     */
    public function initDefault(): OptionsInterface
    {
        $this->addHeaders(static::DEFAULT_HEADERS);
        $this->addAuthorizationHeaders();
        $this->setModel(static::DEFAULT_MODEL);

        return $this;
    }

    /**
     * Get Number of variants returned by OpenAI model (default 1)
     *
     * @return int
     */
    public function getNumberOfResultOptions(): int
    {
        return $this->numberOfResultOptions;
    }

    /**
     * Set Number of variants returned by OpenAI model (default 1)
     *
     * @param int $value
     * @return OptionsInterface
     */
    public function setNumberOfResultOptions(int $value): OptionsInterface
    {
        $this->numberOfResultOptions = $value;

        return $this;
    }

    /**
     * What sampling temperature to use, between 0 and 2.
     * Higher values like 0.8 will make the output more random, while lower values like 0.2
     * will make it more focused and deterministic.
     *
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * Correct value between 0 and 2.
     *
     * @param float $value
     * @return OptionsInterface
     */
    public function setTemperature(float $value): OptionsInterface
    {
        if ($value < static::TEMP_LOWEST) {
            $value = static::TEMP_LOWEST;
        } elseif ($value > static::TEMP_HIGHEST) {
            $value = static::TEMP_HIGHEST;
        }

        $this->temperature = $value;

        return $this;
    }

    /**
     * The maximum number of tokens to generate in the completion.
     * The token count of your prompt plus max_tokens cannot exceed the model's context length.
     *
     * @return int
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens > 0 ? $this->maxTokens : static::DEFAULT_MAX_TOKENS;
    }

    /**
     * The maximum number of tokens to generate in the completion.
     * The token count of your prompt plus max_tokens cannot exceed the model's context length.
     *
     * @param int $value
     * @return OptionsInterface
     * @throws InputException
     */
    public function setMaxTokens(int $value): OptionsInterface
    {
        if ($value < 1) {
            throw new InputException(__('Max Tokens value must be greater then 1, %1 provided.', $value));
        }

        $this->maxTokens = $value;

        return $this;
    }

    /**
     * Set path for request.
     * Can be specific for each model. Base part must not be included. No leading slash.
     *
     * @param string $value
     * @return OptionsInterface
     */
    public function setPath(string $value): OptionsInterface
    {
        $this->path = $value;

        return $this;
    }

    /**
     * Get path for request.
     * Can be specific for each model. Base part must not be included. No leading slash.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Convert options to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'headers'     => $this->getHeadersSafe(),
            'model'       => $this->getModel(),
            'path'        => $this->getPath(),
            'temperature' => $this->getTemperature(),
            'max_tokens'  => $this->getMaxTokens(),
            'n'           => $this->getNumberOfResultOptions()
        ];
    }

    /**
     * Convert options from array
     *
     * @param array $data
     * @return OptionsInterface
     * @throws InputException
     */
    public function fromArray(array $data): OptionsInterface
    {
        $this->setHeaders($data['headers'] ?: static::DEFAULT_HEADERS);
        $this->setModel((string)$data['model'] ?: static::DEFAULT_MODEL);
        $this->setPath((string)$data['path'] ?: static::DEFAULT_PATH);
        $this->setTemperature((float)$data['temperature'] ?: static::DEFAULT_TEMPERATURE);
        $this->setMaxTokens((int)$data['max_tokens'] ?: static::DEFAULT_MAX_TOKENS);
        $this->setNumberOfResultOptions((int)$data['n'] ?: static::DEFAULT_NUMBER_OF_RESULT_OPTIONS);

        $this->addAuthorizationHeaders();

        return $this;
    }

    /**
     * Add authorization headers
     */
    protected function addAuthorizationHeaders(): void
    {
        $apiKey = $this->getCustomKey() ?? $this->helper->getApiKey();
        $headers                  = $this->getHeaders();
        $headers['Authorization'] = 'Authorization: Bearer ' . $apiKey;
        $this->setHeaders($headers);
    }

    /**
     * @inheritDoc
     */
    public function getHttpMethod(): ?string
    {
        return $this->httpMethod;
    }

    /**
     * @inheritDoc
     */
    public function setHttpMethod(?string $value): OptionsInterface
    {
        $this->httpMethod = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomKey(): ?string
    {
        return $this->customKey;
    }

    /**
     * @inheritDoc
     */
    public function setCustomKey(?string $value): OptionsInterface
    {
        $this->customKey = $value;

        return $this;
    }
}
