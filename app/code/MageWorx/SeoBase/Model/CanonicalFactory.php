<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Factory class
 *
 * @see \MageWorx\SeoBase\Model\Canonical
 */
class CanonicalFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $map;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $map
     */
    public function __construct(
        ObjectManager $objectManager,
        array $map = []
    ) {
        $this->objectManager = $objectManager;
        $this->map           = $map;
    }

    /**
     *
     * @param string $param
     * @param array $arguments
     * @return \MageWorx\SeoBase\Model\CanonicalInterface
     * @throws \UnexpectedValueException
     */
    public function create(string $param, array $arguments = []): \MageWorx\SeoBase\Model\CanonicalInterface
    {
        if (isset($this->map[$param])) {
            $instance = $this->objectManager->get($this->map[$param]);
        } else {
            $instance = $this->objectManager->get('\MageWorx\SeoBase\Model\Canonical\Simple');
        }

        if (!$instance instanceof \MageWorx\SeoBase\Model\CanonicalInterface) {
            throw new \UnexpectedValueException(
                'Class ' . get_class($instance) . ' should be an instance of \MageWorx\SeoBase\Model\CanonicalInterface'
            );
        }

        if (isset($arguments['fullActionName']) && $instance instanceof \MageWorx\SeoBase\Model\Canonical) {
            $instance->setFullActionName($arguments['fullActionName']);
        }

        return $instance;
    }

    /**
     * @param string $param
     * @return mixed
     */
    public function get(string $param): \MageWorx\SeoBase\Model\CanonicalInterface
    {
        if (isset($this->map[$param])) {
            $instance = $this->objectManager->get($this->map[$param]);
        } else {
            $instance = $this->objectManager->get('\MageWorx\SeoBase\Model\Canonical\Simple');
        }

        if (!$instance instanceof \MageWorx\SeoBase\Model\CanonicalInterface) {
            throw new \UnexpectedValueException(
                'Class ' . get_class($instance) . ' should be an instance of \MageWorx\SeoBase\Model\CanonicalInterface'
            );
        }

        return $instance;
    }
}
