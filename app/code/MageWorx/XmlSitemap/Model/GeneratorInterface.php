<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

/**
 * {@inheritdoc}
 */
interface GeneratorInterface
{
    /**
     * @param int $storeId
     * @param WriterInterface $writer
     * @return mixed
     */
    public function generate($storeId, $writer);

    /**
     * @return int
     */
    public function getCounter();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getName();
}