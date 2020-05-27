<?php

namespace Splosh\SalesRep\Model\Location;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Splosh\SalesRep\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Splosh\SalesRep\Model\ResourceModel\Location\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collection
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collection,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection->create();
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        /** @var \Splosh\SalesRep\Model\Location $location */
        foreach ($items as $location) {
            $this->loadedData[$location->getId()] = $location->getData();
        }

        $data = $this->dataPersistor->get('splosh_salesrep');
        if (!empty($data)) {
            $location = $this->collection->getNewEmptyItem();
            $location->setData($data);
            $this->loadedData[$location->getId()] = $location->getData();
            $this->dataPersistor->clear('splosh_salesrep');
        }

        return $this->loadedData;
    }
}