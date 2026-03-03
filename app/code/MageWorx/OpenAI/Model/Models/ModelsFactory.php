<?php

namespace MageWorx\OpenAI\Model\Models;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\OpenAI\Api\ModelInterface;

class ModelsFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    protected array $types = [];

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types = $types;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $modelType
     * @param array $data
     * @return ModelInterface
     * @throws LocalizedException
     */
    public function create(string $modelType, array $data = []): ModelInterface
    {
        $class = $this->getClassByType($modelType);
        if (empty($class)) {
            throw new LocalizedException(__('Unable to locate corresponding class for the %1 OpenAI model.', $modelType));
        }

        return $this->objectManager->create($class, $data);
    }

    /**
     * @param string $modelType
     * @return string
     */
    private function getClassByType(string $modelType): string
    {
        return $this->types[$modelType] ?? '';
    }
}
