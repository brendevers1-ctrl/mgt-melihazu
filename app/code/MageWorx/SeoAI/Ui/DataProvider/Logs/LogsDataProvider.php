<?php

namespace MageWorx\SeoAI\Ui\DataProvider\Logs;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog\Grid\CollectionFactory;

class LogsDataProvider extends AbstractDataProvider
{
    const LISTING_NAME = 'mageworx_seoai_logs_listing_data_source';

    /**
     * @var \MageWorx\SeoAI\Model\ResourceModel\ProductRequestResponseLog\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection          = $collectionFactory->create();
        $this->storeManager        = $storeManager;
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()
                 ->load();
        }
        $items = $this->getCollection()->toArray();
        if (empty($items['items'])) {
            return [
                'totalRecords' => 0,
                'items'        => []
            ];
        }

        $indexedItems = [];
        $size         = $this->getCollection()->getSize();
        if ($this->getName() == static::LISTING_NAME) {
            $indexedItems = $items['items'];
        } else {
            $isSingleStore = $this->storeManager->isSingleStoreMode();
            foreach ($items['items'] as $item) {
                if (empty($item['entity_id'])) {
                    $size--;
                    continue;
                }
                $item['id_field_name']            = 'entity_id';
                $item['single_store']             = $isSingleStore;
                $indexedItems[$item['entity_id']] = $item;
            }
        }

        $data = [
            'totalRecords' => $size,
            'items'        => $indexedItems,
        ];

        return $data;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
