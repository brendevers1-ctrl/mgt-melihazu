<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Presets;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\OpenAI\Api\Data\PresetGroupInterface;

class PresetGroup extends AbstractExtensibleModel implements PresetGroupInterface
{
    protected $_idFieldName = self::GROUP_ID;

    protected function _construct()
    {
        $this->_init(\MageWorx\OpenAI\Model\ResourceModel\Presets\PresetGroup::class);
    }

    public function getGroupId(): ?int
    {
        return $this->getData(self::GROUP_ID) === null ? null : (int)$this->getData(self::GROUP_ID);
    }

    public function setGroupId(int $groupId): PresetGroupInterface
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    public function setCode(string $code): PresetGroupInterface
    {
        return $this->setData(self::CODE, $code);
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setName(string $name): PresetGroupInterface
    {
        return $this->setData(self::NAME, $name);
    }
}
