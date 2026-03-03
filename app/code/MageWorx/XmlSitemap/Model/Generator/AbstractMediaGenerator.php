<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\Generator;

use MageWorx\XmlSitemap\Model\MediaGeneratorInterface as MediaGeneratorInterfaceAlias;

/**
 * {@inheritdoc}
 */
abstract class AbstractMediaGenerator extends AbstractGenerator implements MediaGeneratorInterfaceAlias
{
    /**
     * @var int
     */
    protected $imageCounter = 0;

    /**
     * @var int
     */
    protected $videoCounter = 0;

    /**
     * @var bool|null
     */
    protected $usePubInMediaUrl;

    /**
     * @return bool
     */
    public function getIsAllowedImages()
    {
        return true;
    }

    /**
     * @param bool|null $value
     */
    public function setUsePubInMediaUrls($value)
    {
        $this->usePubInMediaUrl = $value;
    }

    /**
     * @return int
     */
    public function getImageCounter()
    {
        return $this->imageCounter;
    }

    /**
     * @return bool
     */
    public function getIsAllowedVideo()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getVideoCounter()
    {
        return $this->videoCounter;
    }
}