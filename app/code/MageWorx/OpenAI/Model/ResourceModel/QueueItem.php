<?php

declare(strict_types = 1);

namespace MageWorx\OpenAI\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OpenAI\Api\Data\QueueItemInterface;
use MageWorx\OpenAI\Api\OptionsInterface;
use MageWorx\OpenAI\Api\OptionsInterfaceFactory;
use MageWorx\OpenAI\Api\ResponseInterfaceFactory as ResponseFactory;
use MageWorx\OpenAI\Model\Options;

/**
 * Resource Model for QueueItem
 */
class QueueItem extends AbstractDb
{
    protected Json $jsonSerializer;

    protected string                  $queueRequestDataTable = 'mageworx_openai_request_data';
    protected OptionsInterfaceFactory $requestOptionsFactory;
    protected ResponseFactory         $responseFactory;

    /**
     * Serializable fields
     *
     * @var array
     */
    protected                 $_serializableFields = [
        'additional_data' => [null, null],
        'response'        => [null, null]
    ];
    private DependencyChecker $dependencyChecker;

    public function __construct(
        Context                 $context,
        Json                    $jsonSerializer,
        OptionsInterfaceFactory $requestOptionsFactory,
        ResponseFactory         $responseFactory,
        DependencyChecker       $dependencyChecker,
                                $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->jsonSerializer        = $jsonSerializer;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->responseFactory       = $responseFactory;
        $this->dependencyChecker     = $dependencyChecker;
    }

    /**
     * Define main table and primary key
     */
    protected function _construct()
    {
        // Assuming 'mageworx_openai_queue_item' is your database table name
        // and 'entity_id' is the primary key of the table
        $this->_init('mageworx_openai_queue_item', 'entity_id');
    }

    public function saveRequestData(
        string           $content,
        ?array           $context,
        OptionsInterface $options
    ): int {
        $optionsAsArray = $options->toArray();

        if (is_array($context)) {
            $context = $this->jsonSerializer->serialize($context);
        }

        $data = [
            'content' => $content,
            'context' => $context,
            'options' => $this->jsonSerializer->serialize($optionsAsArray)
        ];

        $this->getConnection()->insert($this->getQueueRequestDataTable(), $data);
        $id = (int)$this->getConnection()->lastInsertId($this->getQueueRequestDataTable());

        return $id;
    }

    /**
     * Get queue request data table name
     *
     * @return string
     * @throws LocalizedException
     */
    public function getQueueRequestDataTable(): string
    {
        if (empty($this->queueRequestDataTable)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty queue request data table name'));
        }
        return $this->getTable($this->queueRequestDataTable);
    }

    /**
     * @param AbstractModel|QueueItemInterface $object
     * @return QueueItem
     * @throws LocalizedException
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        // Loading request data
        if ($object instanceof QueueItemInterface) {
            $this->loadRequestData($object);
            $this->loadResponseObject($object);
        }

        return $this;
    }

    /**
     * @param QueueItemInterface $item
     * @return void
     * @throws LocalizedException
     */
    protected function loadRequestData(QueueItemInterface $item): void
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from($this->getQueueRequestDataTable())
                                 ->where('entity_id = ?', $item->getRequestDataId());

        $data = $connection->fetchRow($select);

        if ($data) {
            $item->setContent($data['content']);
            $item->setContext($this->jsonSerializer->unserialize($data['context']));

            /** @var OptionsInterface|Options $options */
            $options     = $this->requestOptionsFactory->create();
            $optionsData = $this->jsonSerializer->unserialize($data['options']);
            $options->fromArray($optionsData);
            $item->setOptions($options);
        }
    }

    /**
     * Load the response object from the queue item.
     * If the response data is null, a new response object is created.
     * If the response data is an array, a new response object is created from the array.
     * The response object is then set back to the queue item.
     *
     * @param QueueItemInterface $object The queue item to load the response object from
     * @return void
     */
    protected function loadResponseObject(QueueItemInterface $object)
    {
        /** @var \MageWorx\OpenAI\Model\Queue\QueueItem $object */
        $response = $object->getData('response');
        if ($response === null) {
            $response = $this->responseFactory->create();
        } elseif (is_array($response)) {
            $response = $this->responseFactory->create()->fromArray($response);
        } elseif (is_string($response)) {
            $response = $this->responseFactory->create()->fromArray($this->jsonSerializer->unserialize($response));
        }

        $object->setResponse($response);
    }

    /**
     * @param int $itemId
     * @param int[] $dependencies
     * @return void
     */
    public function createDependency(int $itemId, array $dependencies): void
    {
        $this->dependencyChecker->createDependency($itemId, $dependencies);
    }

    /**
     * Get all dependencies ids for a given queue item
     * (all items on which provided item dependent on)
     * If there are no dependencies, an empty array is returned.
     * If there is no such item in the queue, an empty array is returned.
     *
     * @param int $itemId
     * @return array
     * @throws LocalizedException
     */
    public function getDependenciesIds(int $itemId): array
    {
        // Collect all ids of items on which provided item dependent on
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['d' => $this->dependencyChecker->getMainTable()], ['dependency_item_id'])
                                 ->where('d.queue_item_id = ?', $itemId);

        return $connection->fetchCol($select);
    }

    /**
     * Update the status of the queue item with the given ID
     *
     * @param int $itemId ID of the queue item
     * @param int $status New status for the item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus(int $itemId, int $status): void
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();

        $bind  = ['status' => $status];
        $where = ['entity_id = ?' => $itemId];

        $connection->update($tableName, $bind, $where);
    }

    /**
     * Update the status of the queue item with the given ID
     * Move it to the end of queue
     *
     * @param int $itemId ID of the queue item
     * @param int $status New status for the item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatusAndMoveToTheEnd(int $itemId, int $status): void
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();

        $bind  = ['status' => $status, 'position' => new \Zend_Db_Expr('position + 1')];
        $where = ['entity_id = ?' => $itemId];

        $connection->update($tableName, $bind, $where);
    }

    /**
     * Remove completed items older than 3 days
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeCompletedItemsOlderThanThreeDays(): void
    {
        $connection       = $this->getConnection();
        $queueItemTable   = $this->getMainTable();
        $requestDataTable = $this->getTable('mageworx_openai_request_data');

        $subSelect = $connection->select()
                                ->from($queueItemTable, 'request_data_id')
                                ->where('status = ?', QueueItemInterface::STATUS_COMPLETED)
                                ->where('updated_at < ?', new \Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 3 DAY)'));

        // Deleting request data
        $connection->delete($requestDataTable, ['entity_id IN (?)' => $subSelect]);

        $where = [
            'status = ?'     => QueueItemInterface::STATUS_COMPLETED,
            'updated_at < ?' => new \Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 3 DAY)')
        ];

        // Deleting queue items
        $connection->delete($queueItemTable, $where);
    }
}
