<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoAI\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use MageWorx\SeoAI\Api\GeneratorInterface;

class GeneratorFactory
{
    protected ObjectManagerInterface $objectManager;

    /**
     * @var array
     */
    protected array $types;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array                  $types = []
    ) {
        $this->objectManager = $objectManager;
        $this->types         = $types;
    }

    /**
     * @param string $type
     * @return GeneratorInterface
     * @throws LocalizedException
     */
    public function create(string $type): GeneratorInterface
    {
        if (empty($this->types[$type])) {
            throw new LocalizedException(__('Unable to locate instance "%1" .', $type));
        }

        return $this->objectManager->create($this->types[$type]);
    }
}
