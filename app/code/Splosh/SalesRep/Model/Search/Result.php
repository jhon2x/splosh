<?php

namespace Splosh\SalesRep\Model\Search;

use Splosh\SalesRep\Model\ResourceModel\Location\CollectionFactory as LocationCollection;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\SploshExo\Helper\Data;
use Splosh\SalesRep\Helper\Structure;
use Acidgreen\SploshExo\Model\Staff;

class Result
{
    /**
     * @var LocationCollection
     */
    protected $collection;

    /**
     * @var RegionCollection
     */
    protected $regionCollection;

    /**
     * @var Staff
     */
    protected $staff;

    /**
     * @var Structure
     */
    protected $structureHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $toCheckColumns = [
        Structure::STATE,
        Structure::POSTCODE,
        Structure::SUBURB
    ];

    /**
     * Result constructor.
     * @param LocationCollection $collection
     * @param RegionCollection $regionCollection
     * @param StoreManagerInterface $storeManager
     * @param Staff $staff
     * @param Structure $structureHelper
     */
    public function __construct(
        LocationCollection $collection,
        RegionCollection $regionCollection,
        StoreManagerInterface $storeManager,
        Staff $staff,
        Structure $structureHelper
    )
    {
        $this->collection = $collection->create();
        $this->regionCollection = $regionCollection;
        $this->storeManager = $storeManager;
        $this->staff = $staff;
        $this->structureHelper = $structureHelper;
    }

    /**
     * @param string $query
     * @return array
     */
    public function getMatchedSalesRep($query = '')
    {
        $collection = $this->collection;
        $locationMappings = $collection->toArray();
        $matchedSalesReps = [];

        foreach ($locationMappings['items'] as $item => $value) {
            foreach ($this->toCheckColumns as $column) {
                $toMatchQuery = strtolower(trim($query));
                $toMatchData = $this->filterData($value[$column], $column);
                $isMatchedFlag = in_array($toMatchQuery, $toMatchData);
                if ($isMatchedFlag) {
                    $staff = $this->getStaffData($value[Structure::STAFF_ID]);
                    $matchedSalesReps[] = $staff;
                    continue 2;
                }
            }
        }

        return $matchedSalesReps;
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function getStatesLabels($ids)
    {
        $result = [];
        $regionCollection = $this->regionCollection;
        $regionCollection
            ->addFieldToFilter('main_table.region_id', ['in' => $ids])
            ->getColumnValues('default_name');

        foreach ($regionCollection as $item => $value) {
            $result[] = strtolower($value->getDefaultName());
        }
        return $result;
    }

    /**
     * @param $fileName
     * @return string
     */
    protected function getFilePath($fileName)
    {
        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $imageUrl = $mediaUrl . Data::PATH_BASE_STAFF_PHOTO . '/' . $fileName;
        return $imageUrl;
    }

    /**
     * @param array|string $data
     * @param $column
     * @return array
     */
    protected function filterData($data, $column)
    {

        $isDataArray = is_array($data);

        if (!$isDataArray) {
            $data = explode(',', $data);
        }

        $dataArray = array_map('strtolower', $data);

        if ($column == 'state') {
            $dataArray = $this->getStatesLabels($dataArray);
        }

        return $dataArray;
    }

    /**
     * @param $staff_id
     * @return array
     */
    protected function getStaffData($staff_id)
    {
        $staffData = [];
        $staff = $this->staff->load($staff_id);
        foreach ($this->structureHelper->getStaffColumns() as $item) {
            $staffData[$item] = $staff->getData($item);
            if ($item == 'photo' && $staff->getData($item)) {
                $staffData[$item] = $this->getFilePath($staff->getData($item));
            }
        }
        return $staffData;
    }
}
