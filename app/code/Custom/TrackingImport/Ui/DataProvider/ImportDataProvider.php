<?php
namespace Custom\TrackingImport\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Custom\TrackingImport\Model\ResourceModel\Import\CollectionFactory;

class ImportDataProvider extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
