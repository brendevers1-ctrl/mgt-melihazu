<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

/**
 * {@inheritdoc}
 */
interface MediaGeneratorInterface extends GeneratorInterface
{
    /**
     * @param bool|null $value
     */
    public function setUsePubInMediaUrls($value);

    /**
     * @return bool
     */
    public function getIsAllowedImages();

    /**
     * @return int
     */
    public function getImageCounter();

    /**
     * @return bool
     */
    public function getIsAllowedVideo();

    /**
     * @return int
     */
    public function getVideoCounter();
}