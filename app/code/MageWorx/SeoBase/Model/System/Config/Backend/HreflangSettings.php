<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\SeoBase\Model\System\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized;
use MageWorx\SeoBase\Model\HreflangsConfigReader;

class HreflangSettings extends Serialized
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * HreflangSettings constructor.
     *
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * @return Serialized
     * @noinspection PhpStrictTypeCheckingInspection
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $this->setValue($this->prepareValue($value));
        }

        return parent::beforeSave();
    }

    /**
     * @param array $value
     * @return array
     */
    protected function prepareValue(array $value): array
    {
        $result = [];
        unset($value['__empty']);

        foreach ($value as $row) {
            if (!is_array($row) || !array_key_exists(HreflangsConfigReader::STORE, $row)) {
                continue;
            }

            $result[$row[HreflangsConfigReader::STORE]] = $row;
        }

        return $result;
    }

    /**
     * @noinspection PhpStrictTypeCheckingInspection
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();

        if (!is_array($value)) {
            try {
                $value = empty($value) ? false : $this->serializer->unserialize($value);

                if (is_array($value)) {
                    unset($value['__empty']);
                    $this->setValue($this->encodeRowIds($value));
                } else {
                    $this->setValue('');
                }
            } catch (\Exception $e) {
                $this->_logger->critical(
                    sprintf(
                        'Failed to unserialize %s config value. The error is: %s',
                        $this->getPath(),
                        $e->getMessage()
                    )
                );
                $this->setValue('');
            }
        }
    }

    /**
     * @param array $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function encodeRowIds(array $value): array
    {
        $result = [];
        foreach ($value as $row) {
            $rowId          = $this->mathRandom->getUniqueHash('_');
            $result[$rowId] = $row;
        }

        return $result;
    }
}
