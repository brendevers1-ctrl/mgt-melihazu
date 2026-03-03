<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Factory class
 *
 * @see \MageWorx\SeoXTemplates\Model\DbWriter
 */
class LayeredFiltersProviderFactory
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
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $map
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        ObjectManager $objectManager,
        array $map = []
    ) {
        $this->objectManager = $objectManager;
        $this->map           = $map;
        $this->state         = $state;
    }

    /**
     *
     * @param array $arguments
     * @return \MageWorx\SeoXTemplates\Model\LayeredFiltersProviderInterface
     * @throws \UnexpectedValueException
     */
    public function create(array $arguments = [])
    {
        $areaCode = $this->state->getAreaCode();

        if (isset($this->map[$areaCode])) {
            $instance = $this->objectManager->create($this->map[$areaCode], $arguments);
        } else {
            $instance = $this->objectManager->create(
                \MageWorx\SeoXTemplates\Model\LayeredFiltersProvider\Regular::class
            );
        }

        if (!$instance instanceof \MageWorx\SeoXTemplates\Model\LayeredFiltersProviderInterface) {
            throw new \UnexpectedValueException(
                'Class ' . get_class(
                    $instance
                ) . ' should be an instance of \MageWorx\SeoXTemplates\Model\LayeredFiltersProviderInterface'
            );
        }

        return $instance;
    }
}
