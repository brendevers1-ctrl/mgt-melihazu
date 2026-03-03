<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\Presets;

use Magento\Framework\Model\AbstractModel;
use MageWorx\OpenAI\Api\Data\PresetInterface;

/**
 * Class Preset
 *
 * This class represents a preset, which is a predefined configuration
 * used in the application.
 */
class Preset extends AbstractModel implements PresetInterface
{
    /**
     * Initializes the resource model for the Preset entity
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageWorx\OpenAI\Model\ResourceModel\Presets\Preset::class);
    }

    /**
     * Retrieves the entity ID from the data object.
     * If the entity ID is not set, it returns null.
     *
     * @return int|null The entity ID or null if not set.
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID) === null ? null : (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * Set the entity ID.
     *
     * @param mixed $entityId The entity ID to set.
     * @return PresetInterface Returns the instance of the object implementing the PresetInterface.
     */
    public function setEntityId($entityId): PresetInterface
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Retrieves the preset unique code associated with the current object.
     *
     * @return string|null The code associated with the current object, or null if no code is set.
     */
    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }

    /**
     * Sets the code for the preset.
     *
     * @param string $code The code to set for the preset.
     * @return PresetInterface The instance of PresetInterface.
     */
    public function setCode(string $code): PresetInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Retrieves the preset group ID for the current object.
     *
     * @return int|null The group ID or null if not set
     */
    public function getGroupId(): ?int
    {
        return $this->getData(self::GROUP_ID) === null ? null : (int)$this->getData(self::GROUP_ID);
    }

    /**
     * Sets the preset group ID for the preset.
     *
     * @param int $groupId The group ID to set.
     * @return PresetInterface The updated preset instance.
     */
    public function setGroupId(int $groupId): PresetInterface
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Retrieves the store ID associated with the current entity.
     *
     * The store ID is used to determine the specific store context in which the entity operates.
     * This method returns an integer representation of the store ID, or null if it is not set.
     *
     * @return int|null The store ID associated with the entity, or null if not set.
     */
    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID) === null ? null : (int)$this->getData(self::STORE_ID);
    }

    /**
     * Set the store ID for the preset.
     *
     * @param int $storeId The store Id to set.
     * @return PresetInterface Returns the updated PresetInterface instance.
     */
    public function setStoreId(int $storeId): PresetInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Retrieves the content value of the current instance.
     *
     * @return ?string The content value of the current instance if it exists, otherwise null.
     */
    public function getContent(): ?string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Set the content of the PresetInterface object.
     *
     * @param string $content The content to be set.
     * @return PresetInterface Returns the updated PresetInterface object.
     */
    public function setContent(string $content): PresetInterface
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Retrieves the value of the "created_at" property.
     *
     * @return string|null The value of the "created_at" property, or null if it has not been set.
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Sets the value for the "created_at" property of the PresetInterface object.
     *
     * @param string $createdAt The value to set for the "created_at" property.
     * @return PresetInterface The updated PresetInterface object.
     */
    public function setCreatedAt(string $createdAt): PresetInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Retrieves the value of the "updatedAt" property.
     *
     * @return string|null The value of the "updatedAt" property, or null if it has not been set.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set the value of UPDATED_AT property.
     *
     * @param string $updatedAt The updated at value to set.
     *
     * @return PresetInterface Returns the instance of PresetInterface for method chaining.
     */
    public function setUpdatedAt(string $updatedAt): PresetInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Retrieve the name of the preset visible for a customer.
     *
     * @return string|null The name of the object or null if it is not set.
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Sets the name of the preset visible for a customer.
     *
     * @param string $name The name of the preset.
     * @return PresetInterface The updated object.
     */
    public function setName(string $name): PresetInterface
    {
        return $this->setData(self::NAME, $name);
    }
}
