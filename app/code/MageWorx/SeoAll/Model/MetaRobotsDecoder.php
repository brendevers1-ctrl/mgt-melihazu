<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model;

use MageWorx\SeoAll\Model\Source\EncryptedMetaRobots;

/**
 * Class MetaRobotsDecoder
 */
class MetaRobotsDecoder
{
    /**
     * @param array $metaRobots
     * @return array
     */
    public function decodeMetaRobots($metaRobots)
    {
        $result = [];

        foreach ($metaRobots as $value) {

            $result[] = \str_replace(
                EncryptedMetaRobots::META_ROBOTS_VALUE_DELIMITER,
                ',',
                $value
            );
        }

        return $result;
    }
}
