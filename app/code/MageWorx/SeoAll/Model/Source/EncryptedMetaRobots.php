<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoAll\Model\Source;

/**
 * Class EncryptedMetaRobots
 *
 * Magento use "," for compact values of multi select field to config.
 * So we should use different delimiter inside meta robots.
 */
class EncryptedMetaRobots extends MetaRobots
{
    const META_ROBOTS_VALUE_DELIMITER = '|';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (parent::getAllOptions() as $option) {

            if (!$option['value']) {
                continue;
            }

            $option['value'] = \str_replace(',', self::META_ROBOTS_VALUE_DELIMITER, $option['value']);

            $result[] = $option;
        }

//        var_dump($result); exit;

        return $result;
    }
}
