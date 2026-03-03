<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store as StoreModel;

/**
 * {@inheritdoc}
 */
interface WriterInterface
{
    /**
     * @param string $filePath
     * @param string $fileDir
     * @param string $fileName
     * @param string $tempFilePath
     * @param bool $storeBaseUrl
     * @param int $storeId
     * @param string $serverPath
     * @return mixed|void
     * @throws LocalizedException
     */
    public function init(
        $filePath,
        $fileDir,
        $fileName,
        $tempFilePath,
        $storeBaseUrl = false,
        $storeId = StoreModel::DEFAULT_STORE_ID,
        $serverPath = ''
    );

    /**
     * @param string $rawUrl
     * @param string $lastmod
     * @param string $changefreq
     * @param string $priority
     * @param DataObject|false $imageUrls
     * @param DataObject|false $videoUrls
     * @return mixed|void
     * @throws LocalizedException
     */
    public function write($rawUrl, $lastmod, $changefreq, $priority, $imageUrls = false, $videoUrls = false);

    /**
     * Write header
     */
    public function startWriteXml();

    /**
     * Close file and generate index file
     */
    public function endWriteXml();
}