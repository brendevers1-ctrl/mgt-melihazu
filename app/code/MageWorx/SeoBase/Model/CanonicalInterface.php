<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoBase\Model;

/**
 * @api
 */
interface CanonicalInterface
{
    public function getCanonicalUrl();

    /**
     * @param int $entityId
     * @return mixed
     */
    public function getCanonicalStoreId($entityId);
}
