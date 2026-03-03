<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\SeoAI\Api\Data;

interface ProductRequestResponseLogInterface extends RequestResponseLogInterface
{
    public function setProductId(int $value): ProductRequestResponseLogInterface;
    public function getProductId(): ?int;
}
