<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\XmlSitemap\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use MageWorx\XmlSitemap\Helper\Data;

/**
 * {@inheritdoc}
 */
class Page extends \Magento\Sitemap\Model\ResourceModel\Cms\Page
{
    /**
     * @var Data
     */
    protected $helperSitemap;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Page constructor.
     *
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param Data $helperData
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        Data $helperData,
        ManagerInterface $eventManager,
        string $connectionName = null
    ) {
        $this->helperSitemap = $helperData;
        $this->eventManager  = $eventManager;

        parent::__construct($context, $metadataPool, $entityManager, $connectionName);
    }

    /**
     * Retrieve cms page collection array
     *
     * @param int $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField      = $entityMetadata->getLinkField();

        $columns = [
            'page_id'    => $this->getIdFieldName(),
            'url'        => 'identifier',
            'updated_at' => 'update_time',
        ];

        if ($this->getConnection()->tableColumnExists(
            $this->getMainTable(),
            'mageworx_hreflang_identifier'
        )) {
            $columns['mageworx_hreflang_identifier'] = 'mageworx_hreflang_identifier';
        }

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            $columns
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.in_xml_sitemap = 1'
        )->where(
            'main_table.identifier != ?',
            \Magento\Cms\Model\Page::NOROUTE_PAGE_ID
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        $metaRobotsExclusionList = $this->helperSitemap->getMetaRobotsExclusion();

        if ($metaRobotsExclusionList && $this->getConnection()->tableColumnExists(
                $this->getMainTable(),
                'meta_robots'
            )) {
            $select->where('main_table.meta_robots NOT IN(?)', $metaRobotsExclusionList);
        }

        $this->eventManager->dispatch(
            'mageworx_xmlsitemap_cms_page_generation_before',
            ['select' => $select, 'store_id' => $storeId]
        );

        $cmsPages = [];
        $query    = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $page                     = $this->_prepareObject($row);
            $cmsPages[$page->getId()] = $page;
        }

        return $cmsPages;
    }

    /**
     * Prepare page object
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareObject(array $data)
    {
        $object = parent::_prepareObject($data);
        $object->setPageId($data[$this->getIdFieldName()]);

        $hreflangIdentifier = !empty($data['mageworx_hreflang_identifier']) ? $data['mageworx_hreflang_identifier'] : '';

        $object->setMageworxHreflangIdentifier($hreflangIdentifier);

        return $object;
    }
}
