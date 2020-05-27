<?php

namespace Acidgreen\SploshExo\Model\Staff;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Acidgreen\SploshExo\Model\ResourceModel\Staff\CollectionFactory as StaffCollection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshExo\Helper\Data;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Acidgreen\SploshExo\Model\ResourceModel\Staff\Collection
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DataProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param StaffCollection $staffCollection
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        StaffCollection $staffCollection,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $staffCollection->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
    }

    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getPhotoBasePath()
    {
        return Data::PATH_BASE_STAFF_PHOTO . '/';
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

        foreach ($items as $staff) {
            $this->loadedData[$staff->getId()] = $staff->getData();

            if ($staff->getPhoto()) {
                $photo['photo'][0]['name'] = $staff->getPhoto();
                $photo['photo'][0]['url'] = $this->getMediaBaseUrl() . $this->getPhotoBasePath() . $staff->getPhoto();

                $staffData = $this->loadedData;
                $this->loadedData[$staff->getId()] = array_merge($staffData[$staff->getId()], $photo);
            }
        }

        $data = $this->dataPersistor->get('acidgreen_sploshexo');
        if (!empty($data)) {
            $staff = $this->collection->getNewEmptyItem();
            $staff->setData($data);
            $this->loadedData[$staff->getId()] = $staff->getData();
            $this->dataPersistor->clear('acidgreen_sploshexo');
        }

        return $this->loadedData;
    }
}