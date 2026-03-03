<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use UnexpectedValueException;

/**
 * {@inheritdoc}
 */
class GeneratorFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $generators;

    /**
     * Factory constructor
     *
     * @param ObjectManager $objectManager
     * @param array $generators
     */
    public function __construct(
        ObjectManager $objectManager,
        array $generators = []
    ) {
        $this->objectManager = $objectManager;
        $this->generators    = $generators;
    }

    /**
     *
     * @param string $param
     * @param array $arguments
     * @return GeneratorInterface
     * @throws UnexpectedValueException
     */
    public function create($param, array $arguments = [])
    {
        if (isset($this->generators[$param])) {
            $instance = $this->objectManager->create($this->generators[$param], $arguments);
        } else {
            return null;
        }

        if (!$instance instanceof GeneratorInterface) {
            throw new UnexpectedValueException(
                'Class ' . get_class(
                    $instance
                ) . ' should be an instance of \MageWorx\XmlSitemap\Model\GeneratorInterface'
            );
        }

        return $instance;
    }

    /**
     * @return array of all generators
     */
    public function getAllGenerators()
    {
        $data = [];

        foreach ($this->generators as $generatorCode => $model) {
            $data[$generatorCode] = $this->create($generatorCode);
        }

        return $data;
    }
}
