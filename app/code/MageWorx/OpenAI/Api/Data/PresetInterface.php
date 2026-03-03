<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api\Data;

interface PresetInterface
{
    public const ENTITY_ID  = 'entity_id';
    public const CODE       = 'code';
    public const NAME       = 'name';
    public const GROUP_ID   = 'group_id';
    public const STORE_ID   = 'store_id';
    public const CONTENT    = 'content';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Retrieves the entity ID associated with this preset.
     *
     * @return int|null The entity ID if available, null otherwise.
     */
    public function getEntityId();

    /**
     * Sets the entity ID for this preset.
     *
     * @param int $entityId The entity ID to be set.
     * @return PresetInterface The modified object with the updated entity ID.
     */
    public function setEntityId($entityId): PresetInterface;

    public function getCode(): ?string;

    public function setCode(string $code): PresetInterface;

    public function getGroupId(): ?int;

    public function setGroupId(int $groupId): PresetInterface;

    public function getStoreId(): ?int;

    public function setStoreId(int $storeId): PresetInterface;

    public function getContent(): ?string;

    public function setContent(string $content): PresetInterface;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(string $createdAt): PresetInterface;

    public function getUpdatedAt(): ?string;

    public function setUpdatedAt(string $updatedAt): PresetInterface;

    /**
     * Get preset name visible to customer.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set preset name visible to customer.
     *
     * @param string $name
     * @return PresetInterface
     */
    public function setName(string $name): PresetInterface;
}
