<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types = 1);

namespace MageWorx\OpenAI\Ui\DataProvider\Process\InnerView;

use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\Collection;
use MageWorx\OpenAI\Model\ResourceModel\QueueItem\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    const LISTING_NAME = 'mageworx_openai_process_inner_view_listing_data_source';

    /**
     * @var Collection
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

    protected StoreManagerInterface $storeManager;
    protected Http                  $request;

    protected ?int $currentProcessId    = null;
    protected bool $collectionProcessed = false;

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
        Http $request,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection          = $collectionFactory->create();
        $this->storeManager        = $storeManager;
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    public function getCollection()
    {
        if (!$this->collectionProcessed) {
            $currentProcessId = $this->getCurrentProcessId();
            if ($currentProcessId !== null) {
                $this->collection
                    ->addFieldToFilter('process_id', $currentProcessId);
            }

            $this->collectionProcessed = true;
        }

        return parent::getCollection();
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

    /**
     * @return int|null
     */
    protected function getCurrentProcessId(): ?int
    {
        if ($this->currentProcessId === null) {
            $fieldNames = [$this->getRequestFieldName(), 'process_id'];
            foreach ($fieldNames as $fieldName) {
                $processId = (int)$this->request->getParam($fieldName);
                if ($processId) {
                    $this->currentProcessId = $processId;
                    break;
                }
            }
        }

        return $this->currentProcessId;
    }
}
