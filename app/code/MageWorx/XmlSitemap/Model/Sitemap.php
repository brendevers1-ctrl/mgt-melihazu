<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\StringLength;
use Magento\MediaStorage\Model\File\Validator\AvailablePath;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\XmlSitemap\Helper\Data as HelperData;
use MageWorx\XmlSitemap\Helper\MagentoSitemap as MagentoSitemapHelper;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\Collection;
use MageWorx\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;
use MageWorx\XmlSitemap\Model\LinkChecker;
use SimpleXMLElement;

/**
 * {@inheritdoc}
 */
class Sitemap extends AbstractModel
{
    /**
     * Maximum length of sitemap filename
     */
    const MAX_FILENAME_LENGTH = 64;

    /**
     * @var GeneratorManager
     */
    protected $generatorManager;

    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SitemapCollectionFactory
     */
    protected $sitemapCollectionFactory;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var AvailablePath
     */
    protected $pathValidator;

    /**
     * @var MagentoSitemapHelper
     */
    protected $helperMagentoSitemap;

    /**
     * @var StringLength
     */
    protected $stringValidator;

    /**
     * @var LinkChecker
     */
    protected $linkChecker;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Sitemap constructor.
     *
     * @param GeneratorManager $generator
     * @param Filesystem $filesystem
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param SitemapCollectionFactory $sitemapCollectionFactory
     * @param Escaper $escaper
     * @param AvailablePath $pathValidator
     * @param MagentoSitemapHelper $helperMagentoSitemap
     * @param StringLength $stringValidator
     * @param \MageWorx\XmlSitemap\Model\LinkChecker $linkChecker
     * @param HelperData $helperData
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @throws FileSystemException
     */
    public function __construct(
        GeneratorManager $generator,
        Filesystem $filesystem,
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        SitemapCollectionFactory $sitemapCollectionFactory,
        Escaper $escaper,
        AvailablePath $pathValidator,
        MagentoSitemapHelper $helperMagentoSitemap,
        StringLength $stringValidator,
        LinkChecker $linkChecker,
        HelperData $helperData,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->stringValidator          = $stringValidator;
        $this->helperMagentoSitemap     = $helperMagentoSitemap;
        $this->pathValidator            = $pathValidator;
        $this->escaper                  = $escaper;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->storeManager             = $storeManager;
        $this->generatorManager         = $generator;
        $this->linkChecker              = $linkChecker;
        $this->helper                   = $helperData;
        $this->filesystem               = $filesystem;
        $this->directory                = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Generate sitemap xml file
     */
    public function generateXml()
    {
        $this->generatorManager->generateXml($this);
    }

    /**
     * @return string
     */
    public function getServerPath()
    {
        return $this->getData('server_path');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setServerPath($value)
    {
        return $this->setData('server_path', $value);
    }

    /**
     * @return string
     */
    public function getSitemapPath(): string
    {
        return $this->getData('sitemap_path');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSitemapLink($value)
    {
        return $this->setData('sitemap_link', $value);
    }

    /**
     * @return string
     */
    public function getSitemapLink()
    {
        return $this->getData('sitemap_link');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSitemapPath($value)
    {
        return $this->setData('sitemap_path', $value);
    }

    /**
     * @return string
     */
    public function getSitemapFilename(): string
    {
        return $this->getData('sitemap_filename');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setSitemapFilename($value)
    {
        return $this->setData('sitemap_filename', $value);
    }

    /**
     * @return string
     */
    public function getSitemapTime(): string
    {
        return (string)$this->getData('sitemap_time');
    }

    /**
     * @param string $date
     * @return Sitemap
     */
    public function setSitemapTime($date)
    {
        return $this->setData('sitemap_time', $date);
    }


    /**
     * @return mixed
     */
    public function getCountByEntity(): string
    {
        return $this->getData('count_by_entity');
    }

    /**
     * @param array|string $data
     * @return Sitemap
     */
    public function setCountByEntity($data)
    {
        return $this->setData('count_by_entity', $data);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return trim($this->getServerPath(), '/') . $this->getSitemapPath();
    }

    /**
     * @return string
     */
    public function getPathFilename()
    {
        return ltrim($this->getPath() . $this->getSitemapFilename(), '/');
    }

    /**
     * @return string
     */
    public function getPathFilenameFromServerDirectory()
    {
        return ltrim($this->getSitemapPath() . $this->getSitemapFilename(), '/');
    }

    /**
     * @param string $path
     * @return string
     */
    public function getFullPath($path = false): string
    {
        if (!$path) {
            $path = $this->getPath();
        }

        return $path ? $this->getBaseDir() . $path : $this->getBaseDir();
    }

    /**
     * @param string $sitemapFilename
     * @param string $path
     * @return string
     */
    public function getFullPathFilename($sitemapFilename = '', $path = ''): string
    {
        if (!$sitemapFilename) {
            $sitemapFilename = $this->getSitemapFilename();
        }

        return $this->getFullPath($path) . $sitemapFilename;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->validate();
        $this->generateLinkToSitemap();

        return $this;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function validate()
    {
        $this->normalizeParams();

        if (!$this->getStoreId()) {
            throw new LocalizedException(__('Please choose store view'));
        }

        $this->validateFileName();
        $this->addXmlExtensionToFileName();
        $this->validatePath();
        $this->checkFileNameIsOriginal();

        return true;
    }

    /**
     * @return void
     */
    public function normalizeParams()
    {
        $this->setSitemapPath(
            $this->getNormalizedSitemapPath()
        );

        $this->setServerPath(
            $this->getNormalizedServerPath()
        );
    }

    /**
     * @return $this
     */
    public function beforeDelete()
    {
        $this->removeFiles();

        return parent::beforeDelete();
    }

    /**
     * @return void
     */
    public function removeFiles()
    {
        if ($this->getSitemapFilename() && file_exists($this->getFullPathFilename())) {
            $filePathNames = [$this->getFullPathFilename()];

            $fileNames = $this->getFileNamesFromSitemapIndex();

            foreach ($fileNames as $fileName) {
                $filePathNames[] = $this->getFullPathFilename($fileName);
            }

            foreach ($filePathNames as $fullPathName) {
                if (file_exists($fullPathName)) {
                    unlink($fullPathName);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function addXmlExtensionToFileName()
    {
        if (!preg_match('#\.xml$#', $this->getSitemapFilename())) {
            $this->setSitemapFilename($this->getSitemapFilename() . '.xml');
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function checkFileNameIsOriginal()
    {
        $otherFilePath = $this->getOtherModelFullPathFilenames();

        $duplicate = array_search($this->getFullPathFilename(), $otherFilePath);
        if ($duplicate) {
            throw new LocalizedException(
                __(
                    "The file with such name '%1' exists (sitemap id = %2). Please, select other name for the file.",
                    $otherFilePath[$duplicate],
                    $duplicate
                )
            );
        }
    }

    /**
     * @return string[]
     */
    protected function getOtherModelFullPathFilenames()
    {
        /** @var Collection $collection */
        $collection = $this->sitemapCollectionFactory->create();
        $collection->addFieldToFilter('sitemap_id', ['neq' => $this->getSitemapId()]);

        $filePaths = [];
        /** @var Sitemap $model */
        foreach ($collection as $model) {
            $filePaths[$model->getSitemapId()] = $this->getFullPathFilename(
                $model->getSitemapFilename(),
                $model->getPath()
            );
        }

        return $filePaths;
    }

    /**
     * @return string[]
     */
    protected function getFileNamesFromSitemapIndex()
    {
        $fileNames = [];

        $sxml = simplexml_load_file($this->getFullPathFilename());

        if ($sxml) {
            $i = 0;

            while (is_object($sxml->sitemap[$i]) && $sxml->sitemap[$i]->loc instanceof SimpleXMLElement) {
                $el      = $sxml->sitemap[$i]->loc;
                $fileUrl = $el->__toString();
                if ($fileUrl != "" && preg_match('/_([0-9]){3}.xml$/', $fileUrl)) {
                    $urlParts    = explode("/", $fileUrl);
                    $fileName    = array_pop($urlParts);
                    $fileNames[] = $fileName;
                }

                $i++;
            }
        }

        return $fileNames;
    }

    /**
     * Get base dir
     *
     * @return string
     */
    protected function getBaseDir()
    {
        return $this->directory->getAbsolutePath();
    }

    /**
     * @param string $realPath
     * @return string
     */
    protected function prepareSitemapPath($realPath)
    {
        return rtrim(str_replace(str_replace('\\', '/', $this->getBaseDir()), '', $realPath), '/');
    }

    /**
     * @return string
     */
    public function getFullTempPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
    }

    /**
     * @param string $type
     * @return string
     */
    public function getStoreBaseUrl($type = UrlInterface::URL_TYPE_LINK)
    {
        $store    = $this->storeManager->getStore($this->getStoreId());
        $isSecure = $store->isUrlSecure();

        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     * @return mixed
     */
    public function hasPubMediaUrl()
    {
        return strpos('/' . trim($this->getData('server_path'), '/') . '/', '/pub/') === false;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateFileName()
    {
        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getSitemapFilename())) {
            throw new LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in the filename. No spaces or other characters are allowed.'
                )
            );
        }

        $this->stringValidator->setMax(self::MAX_FILENAME_LENGTH);
        if (!$this->stringValidator->isValid($this->getSitemapFilename())) {
            foreach ($this->stringValidator->getMessages() as $message) {
                throw new LocalizedException(__($message));
            }
        }
    }


    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validatePath()
    {
        $realPath = $this->getPath();

        if (!$realPath && preg_match('#\.\.[\\\/]#', $realPath)) {
            throw new LocalizedException(__('Please define correct path'));
        }

        if (!$this->directory->isExist($realPath)) {
            throw new LocalizedException(
                __(
                    'Please create the specified folder "%1" before saving the sitemap.',
                    $this->getPath()
                )
            );
        }

        $this->pathValidator->setPaths($this->helperMagentoSitemap->getValidPaths());
        $pathToFile = rtrim($realPath, '\\/') . '/' . $this->getSitemapFilename();

        if (!$this->pathValidator->isValid($pathToFile)) {
            foreach ($this->pathValidator->getMessages() as $message) {
                throw new LocalizedException(__($message));
            }
        }

        if (!$this->directory->isWritable($realPath)) {
            throw new LocalizedException(
                __(
                    'Please make sure that "%1" is writable by web-server.',
                    $this->getPath()
                )
            );
        }
    }

    /**
     * @return string
     */
    protected function getNormalizedSitemapPath(): string
    {
        $sitemapPath = trim(
            str_replace(
                str_replace('\\', '/', $this->directory->getAbsolutePath()),
                '',
                $this->getSitemapPath()
            ),
            '/'
        );

        return $sitemapPath ? '/' . $sitemapPath . '/' : '/';
    }

    /**
     * @return string
     */
    protected function getNormalizedServerPath(): string
    {
        $one = str_replace('\\', '/', $this->directory->getAbsolutePath());

        $path =
            str_replace(
                $one,
                '',
                $this->getServerPath()
            );

        if ($path) {
            $path = trim($path, '/') . '/';
        }

        return $path;
    }

    /**
     * @return void
     */
    protected function generateLinkToSitemap()
    {
        $url       = '';
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        if ($directory->isFile($this->getPathFilename())) {

            $store = $this->storeManager->getStore($this->getStoreId());

            $urls['store_url'] = $this->getStoreBaseUrl(
                    UrlInterface::URL_TYPE_LINK
                ) . $this->getPathFilenameFromServerDirectory();

            if ($store->isUseStoreInUrl()) {
                $urls['no_store_code_url'] = str_replace($store->getCode() . '/', '', $urls['store_url']);
            }

            $urls['base_url'] = $this->getStoreBaseUrl(
                    UrlInterface::URL_TYPE_WEB
                ) . $this->getPathFilename();

            if ($this->helper->isCheckUrlsAvailability()) {
                $urlKey = $this->linkChecker->checkUrls($urls, $store->getId());
            }

            $url = !empty($urlKey) ? $urls[$urlKey] : $urls['base_url'];
        }

        $this->setSitemapLink($url);
    }
}
