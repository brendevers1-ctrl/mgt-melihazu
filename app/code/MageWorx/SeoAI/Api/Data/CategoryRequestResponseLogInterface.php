<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api\Data;

interface CategoryRequestResponseLogInterface extends RequestResponseLogInterface
{
    public function setCategoryId(int $value): CategoryRequestResponseLogInterface;
    public function getCategoryId(): ?int;
}
