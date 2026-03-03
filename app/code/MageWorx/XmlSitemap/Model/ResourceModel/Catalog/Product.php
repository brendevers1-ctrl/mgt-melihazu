<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\ResourceModel\Catalog;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sitemap\Helper\Data;
use Magento\Store\Model\Store;
use Magento\Sitemap\Model\Source\Product\Image\IncludeImage;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoAll\Helper\MagentoVersion;
use MageWorx\SeoAll\Model\Source\Product\CanonicalType;
use MageWorx\XmlSitemap\Model\Source\ProductImageSource;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\Framework\DataObject;
use Zend_Db_Statement_Exception;
use function array_keys;

/**
 * {@inheritdoc}
 */
class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product
{
    /**
     * @var Collection
     */
    protected $query;

    /**
     * @var \MageWorx\XmlSitemap\Helper\Data
     */
    protected $helperSitemap;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var bool
     */
    protected $readed = false;

    /**
     * @var bool
     */
    protected $flexibleCanonicalFlag;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;

    /**
     * @var Image
     */
    private $catalogImageHelper;

    /**
     * Scope Config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var bool|null
     */
    protected $usePubInMediaUrls;

    /**
     * @var MagentoVersion
     */
    protected $helperVersion;

    /**
     * Images and video data for current product
     *
     * @var array
     */
    protected $imagesCollection = [];

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Data $sitemapData
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param StoreManagerInterface $storeManager
     * @param Visibility $productVisibility
     * @param Status $productStatus
     * @param Gallery $mediaGalleryResourceModel
     * @param ReadHandler $mediaGalleryReadHandler
     * @param Config $mediaConfig
     * @param \MageWorx\XmlSitemap\Helper\Data $helperSitemap
     * @param ManagerInterface $eventManager
     * @param UrlBuilder $urlBuilder
     * @param bool $flexibleCanonicalFlag
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Data $sitemapData,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        StoreManagerInterface $storeManager,
        Visibility $productVisibility,
        Status $productStatus,
        Gallery $mediaGalleryResourceModel,
        ReadHandler $mediaGalleryReadHandler,
        Config $mediaConfig,
        \MageWorx\XmlSitemap\Helper\Data $helperSitemap,
        ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product $productModel,
        Image $catalogImageHelper,
        MagentoVersion $helperVersion,
        $flexibleCanonicalFlag = false,
        $connectionName = null
    ) {
        $this->helperSitemap         = $helperSitemap;
        $this->eventManager          = $eventManager;
        $this->flexibleCanonicalFlag = $flexibleCanonicalFlag;
        $this->productModel          = $productModel;
        $this->catalogImageHelper    = $catalogImageHelper;
        $this->helperVersion         = $helperVersion;

        parent::__construct(
            $context,
            $sitemapData,
            $productResource,
            $storeManager,
            $productVisibility,
            $productStatus,
            $mediaGalleryResourceModel,
            $mediaGalleryReadHandler,
            $mediaConfig,
            $connectionName
        );
    }

    /**
     * Additional condition related to flexible canonical functionality from SEO Base extension
     *
     * @return string
     */
    protected function getUrlRewriteWhereCondition()
    {
        if (!$this->flexibleCanonicalFlag) {
            return ' AND url_rewrite.metadata IS NULL';
        }

        return '';
    }


    /**
     * Get product collection array
     * Call this function while !isCollectionReaded() to read all collection
     *
     * @param null|string|bool|int|Store $storeId
     * @param int $limit
     * @param bool|null $usePubInMediaUrls
     * @return DataObject[]|null
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getLimitedCollection($storeId, $limit, $usePubInMediaUrls = null)
    {
        $this->usePubInMediaUrls = $usePubInMediaUrls;

        $products = [];

        /* @var $store Store */
        $store = $this->_storeManager->getStore($storeId);
        if (!$store) {
            return false;
        }

        if ($limit <= 0) {
            return false;
        }

        if (!isset($this->query)) {
            $connection = $this->getConnection();

            $whereCondition = $this->getUrlRewriteWhereCondition();

            $this->_select = $connection->select()->from(
                ['e' => $this->getMainTable()],
                [
                    $this->getIdFieldName(),
                    $this->_productResource->getLinkField(),
                    'updated_at'
                ]
            )->joinInner(
                ['w' => $this->getTable('catalog_product_website')],
                'e.entity_id = w.product_id',
                []
            )->joinLeft(
                ['url_rewrite' => $this->getTable('url_rewrite')],
                'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1' . $whereCondition
                . $connection->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
                . $connection->quoteInto(' AND url_rewrite.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE),
                ['url' => 'request_path']
            )->where(
                'w.website_id = ?',
                $store->getWebsiteId()
            );

            $this->_addFilter($store->getId(), 'status', $this->_productStatus->getVisibleStatusIds(), 'in');
            $this->_addFilter($store->getId(), 'visibility', $this->_productVisibility->getVisibleInSiteIds(), 'in');
            $this->_addExtendedFilter($store->getId(), 'in_xml_sitemap', 1, '=');

            //"meta_robots" is attribute added by SEO Base extension
            if ($this->_productResource->getAttribute('meta_robots')) {
                $metaRobotsExclusionList = $this->helperSitemap->getMetaRobotsExclusion();

                if ($metaRobotsExclusionList) {
                    $this->_addExtendedFilter($store->getId(), 'meta_robots', $metaRobotsExclusionList, 'nin');
                }
            }

            if ($this->helperSitemap->isExcludeOutOfStockProducts()) {
                $this->_select->joinInner(
                    ['cataloginventory' => $this->getTable('cataloginventory_stock_status')],
                    'e.entity_id = cataloginventory.product_id AND cataloginventory.stock_status = 1'
                );
            }

            $imageInclude = $this->_sitemapData->getProductImageIncludePolicy($store->getId());
            if (IncludeImage::INCLUDE_NONE != $imageInclude) {
                $this->_joinAttribute($store->getId(), 'name', 'name');

                if (IncludeImage::INCLUDE_ALL == $imageInclude) {
                    $this->_joinAttribute($store->getId(), 'thumbnail', 'thumbnail');
                } elseif (IncludeImage::INCLUDE_BASE == $imageInclude) {
                    $this->_joinAttribute($store->getId(), 'image', 'image');
                }
            }

            $this->eventManager->dispatch(
                'mageworx_xmlsitemap_product_generation_before',
                ['select' => $this->_select, 'store_id' => $storeId]
            );

//            echo $this->_select->__toString();  die();
            $this->query  = $connection->query($this->_select);
            $this->readed = false;
        }

        for ($i = 0; $i < $limit; $i++) {
            if (!$row = $this->query->fetch()) {
                $this->readed = true;
                break;
            }

            $product                     = $this->_prepareProduct($row, $store->getId());
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @param bool $required
     * @param null|string $column
     *
     * @return Select|bool
     * @throws LocalizedException
     */
    protected function _addExtendedFilter(
        $storeId,
        $attributeCode,
        $value,
        $type = '=',
        $required = false,
        $column = null
    ) {
        if (!$this->_select instanceof Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            case 'nin':
                $conditionRule = ' NOT IN(?)';
                break;
            default:
                $conditionRule = '';
                break;
        }

        if (!$conditionRule) {
            return $this->_select;
        }

        $attribute = $this->_getAttribute($attributeCode);
        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_joinAttribute($storeId, $attributeCode, $column);

            if ($attribute['is_global']) {
                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->getConnection()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );

                if ($required) {
                    $where = '(' . $ifCase . ')' . $conditionRule;
                } else {
                    $where = '(' . $ifCase . ')' . $conditionRule . ' OR ' . '(' . $ifCase . ') IS NULL';
                }

                $this->_select->where($where, $value);
            }
        }

        return $this->_select;
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @param int $storeId
     * @return DataObject
     */
    protected function _prepareProduct(array $productRow, $storeId)
    {
        $this->clearImageCollection();
        $product = parent::_prepareProduct($productRow, $storeId);
        $this->_loadProductVideos($product, $storeId);

        return $product;
    }

    /**
     * Load product images
     *
     * Copy from magento/module-sitemap 100.3.3 (magento 2.3.3): reason: rewrite private function getProductImageUrl()
     *
     * @param DataObject $product
     * @param int $storeId
     * @return void
     * @throws FileSystemException
     */
    protected function _loadProductImages($product, $storeId)
    {
        $this->_storeManager->setCurrentStore($storeId);
        /** @var $helper Data */
        $helper             = $this->_sitemapData;
        $imageIncludePolicy = $helper->getProductImageIncludePolicy($storeId);

        // Get product images
        $imagesCollection = [];
        if (IncludeImage::INCLUDE_ALL == $imageIncludePolicy) {
            $imagesCollection = $this->_getAllProductImages($product, $storeId);
        } elseif (IncludeImage::INCLUDE_BASE == $imageIncludePolicy
            && $product->getImage() != self::NOT_SELECTED_IMAGE
            && $product->getImage()
        ) {
            $imagesCollection = [
                new DataObject(
                    ['url' => $this->getProductImageUrl($product->getImage())]
                ),
            ];
        }

        if ($imagesCollection) {
            // Determine thumbnail path
            $thumbnail = $product->getThumbnail();
            if ($thumbnail && $product->getThumbnail() != self::NOT_SELECTED_IMAGE) {
                $thumbnail = $this->getProductImageUrl($thumbnail);
            } else {
                $thumbnail = $imagesCollection[0]->getUrl();
            }

            $product->setImages(
                new DataObject(
                    ['collection' => $imagesCollection, 'title' => $product->getName(), 'thumbnail' => $thumbnail]
                )
            );
        }
    }

    /**
     * @return void
     */
    protected function clearImageCollection()
    {
        $this->imagesCollection = [];
    }

    /**
     * Load product videos
     *
     * @param DataObject $product
     * @param int $storeId
     * @return void
     */
    protected function _loadProductVideos($product, $storeId)
    {
        $imagesCollection = $this->_getAllProductImages($product, $storeId);

        $videoCollection = [];

        /** @var DataObject $mediaItem */
        foreach ($imagesCollection as $mediaItem) {
            if ($this->isValidVideoItem($mediaItem)) {
                $videoCollection[] = $mediaItem;
            }
        }

        if ($videoCollection) {
            $product->setVideos(
                new DataObject(
                    ['collection' => $videoCollection]
                )
            );
        }
    }

    /**
     * Get all product images
     *
     * @param DataObject $product
     * @param int $storeId
     * @return DataObject[]
     */
    protected function _getAllProductImages($product, $storeId)
    {
        $productId = $product->getId();

        if (isset($this->imageCollection[$productId])) {
            return $this->imageCollection[$productId];
        }

        $product->setStoreId($storeId);
        $gallery = $this->mediaGalleryResourceModel->loadProductGalleryByAttributeId(
            $product,
            $this->mediaGalleryReadHandler->getAttribute()->getId()
        );

        $this->imagesCollection[$productId] = [];
        if ($gallery) {
            foreach ($gallery as $image) {
                if (!$image['file']) {
                    continue;
                }
                $this->imagesCollection[$productId][] = new DataObject(
                    [
                        'url'               => $this->getProductImageUrl($image['file']),
                        'caption'           => $image['label'] ? $image['label'] : $image['label_default'],
                        'media_type'        => $image['media_type'],
                        'video'             => $this->_getVideoData($image, 'video_url'),
                        'video_title'       => $this->_getVideoData($image, 'video_title'),
                        'video_description' => $this->_getVideoData($image, 'video_description'),
                    ]
                );
            }
        }

        return $this->imagesCollection[$productId];
    }
    
    /**
     * Get all product images
     *
     * @param array $image
     * @param string $key
     * @return string
     */
    protected function _getVideoData($image, $key)
    {
        if (array_key_exists($key, $image)) {
            return $image[$key] ? $image[$key] : $image[$key . '_default'];
        } else {
            return '';
        }
    }
    

    /**
     * Get product image URL from image filename
     *
     * @param string $image
     * @return string
     * @throws FileSystemException
     */
    protected function getProductImageUrl($image)
    {
        $imageUrl = '';

        //Since Magento 2.3
        if ($this->helperVersion->checkModuleVersion('Magento_Sitemap', '100.3.0')) {

            $imageUrlBuilder = ObjectManager::getInstance()->get('Magento\Catalog\Model\Product\Image\UrlBuilder');

            if ($imageUrlBuilder) {
                $imageUrl = $imageUrlBuilder->getUrl($image, 'product_page_image_large');
            }

        } else {
            $productObject = $this->productModel;
            $imageUrl      = $this->catalogImageHelper
                ->init($productObject, 'product_page_image_large')
                ->setImageFile($image)
                ->getUrl();
        }

        if ($this->helperSitemap->getProductImageSource() == ProductImageSource::ORIGINAL_SOURCE) {

            $imageParts = explode('/', $imageUrl);
            $cacheKeys  = array_keys($imageParts, 'cache');

            if ($cacheKeys) {
                unset($imageParts[$cacheKeys[0]], $imageParts[$cacheKeys[0] + 1]);
            }

            $imageUrl = implode('/', $imageParts);
        }

        return $this->cropPubFromImageUrl($imageUrl);
    }

    /**
     * @param string $image
     * @return string
     */
    protected function cropPubFromImageUrl($image)
    {
        if ($this->usePubInMediaUrls === false) {
            $image = str_replace('/pub/', '/', $image);
        }

        return $image;
    }

    /**
     * @return bool
     */
    public function isCollectionReaded()
    {
        return $this->readed;
    }

    /**
     * @param DataObject $mediaItem
     * @return bool
     */
    protected function isValidVideoItem($mediaItem)
    {
        if ($mediaItem['media_type'] === ExternalVideoEntryConverter::MEDIA_TYPE_CODE &&
            !empty($mediaItem['url']) &&
            !empty($mediaItem['video']) &&
            !empty($mediaItem['video_title']) &&
            !empty($mediaItem['video_description'])
        ) {
            return true;
        }

        return false;
    }
}
