<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Api\Data;

interface PresetGroupInterface
{
    public const GROUP_ID = 'group_id';

    /**
     * Unique identifier for the preset group.
     */
    public const CODE = 'code';

    /**
     * Name of the preset group visible for the customer.
     */
    public const NAME = 'name';

    /**
     * Retrieves the group ID associated with this preset group.
     *
     * @return int|null The group ID if available, null otherwise.
     */
    public function getGroupId(): ?int;

    /**
     * Sets the group ID for this preset group.
     *
     * @param int $groupId The group ID to be set.
     * @return PresetGroupInterface The modified object with the updated group ID.
     */
    public function setGroupId(int $groupId): PresetGroupInterface;

    /**
     * Retrieves the code of the preset group.
     *
     * @return string|null The code of the object, or null if no code is available.
     */
    public function getCode(): ?string;

    /**
     * Set the code for the PresetGroup.
     *
     * @param string $code The code to set for the PresetGroup.
     * @return PresetGroupInterface The updated PresetGroupInterface.
     */
    public function setCode(string $code): PresetGroupInterface;

    /**
     * Retrieves the name of the preset group.
     *
     * @return string|null The name of the object, or null if no name is available.
     */
    public function getName(): ?string;

    /**
     * Set the name for the PresetGroup.
     *
     * @param string $name The name to set for the PresetGroup.
     * @return PresetGroupInterface The updated PresetGroupInterface.
     */
    public function setName(string $name): PresetGroupInterface;
}
