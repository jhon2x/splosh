<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Carrier\Method;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Api\Data\RateInterfaceFactory;
use MageWorx\ShippingRules\Api\RateRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use MageWorx\ShippingRules\Model\ResourceModel\Rate as ResourceRate;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory as RateCollectionFactory;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\ExportCollectionFactory as ExportCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RateRepository
 */
class RateRepository implements RateRepositoryInterface
{
    /**
     * @var ResourceRate
     */
    protected $resource;

    /**
     * @var RateFactory
     */
    protected $rateFactory;

    /**
     * @var RateCollectionFactory
     */
    protected $rateCollectionFactory;

    /**
     * @var ExportCollectionFactory
     */
    protected $rateExportCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var RateInterfaceFactory
     */
    protected $dataRateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceRate $resource
     * @param RateFactory $rateFactory
     * @param RateInterfaceFactory $dataRateFactory
     * @param RateCollectionFactory $rateCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceRate $resource,
        RateFactory $rateFactory,
        RateInterfaceFactory $dataRateFactory,
        RateCollectionFactory $rateCollectionFactory,
        ExportCollectionFactory $rateExportCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource                    = $resource;
        $this->rateFactory                 = $rateFactory;
        $this->rateCollectionFactory       = $rateCollectionFactory;
        $this->rateExportCollectionFactory = $rateExportCollectionFactory;
        $this->searchResultsFactory        = $searchResultsFactory;
        $this->dataObjectHelper            = $dataObjectHelper;
        $this->dataRateFactory             = $dataRateFactory;
        $this->dataObjectProcessor         = $dataObjectProcessor;
        $this->storeManager                = $storeManager;
    }

    /**
     * Save Rate data
     *
     * @param Rate $rate
     * @return Rate
     * @throws CouldNotSaveException
     */
    public function save(Rate $rate)
    {
        try {
            /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $rate */
            $this->resource->save($rate);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the rate: %1',
                    $exception->getMessage()
                )
            );
        }

        return $rate;
    }

    /**
     * Retrieve rate by its code
     *
     * @param string $code
     * @return \MageWorx\ShippingRules\Model\Carrier\Method\Rate|\MageWorx\ShippingRules\Api\Data\RateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode($code)
    {
        /** @var Rate $rate */
        $rate = $this->rateFactory->create();
        $this->resource->load($rate, $code, 'rate_code');
        if (!$rate->getId()) {
            throw new NoSuchEntityException(__('Rate with a code "%1" does not exist.', $code));
        }

        return $rate;
    }

    /**
     * Load Rate data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param bool $returnRawObjects
     * @param bool $exportCollection
     * @return \Magento\Framework\Api\SearchResultsInterface|ResourceRate\Collection
     */
    public function getList(SearchCriteriaInterface $criteria, $returnRawObjects = false, $exportCollection = false)
    {
        /** @var \Magento\Framework\Api\SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        if ($exportCollection) {
            /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Grid\RegularCollection $collection */
            $collection = $this->rateExportCollectionFactory->create();
        } else {
            /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $collection */
            $collection = $this->rateCollectionFactory->create();
        }

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $rates = [];
        /** @var Rate $rateModel */
        foreach ($collection as $rateModel) {
            if ($returnRawObjects) {
                $rates[] = $rateModel;
            } else {
                /** @var RateInterface $rateData */
                $rateData = $this->dataRateFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $rateData,
                    $rateModel->getData(),
                    'MageWorx\ShippingRules\Api\Data\RateInterface'
                );
                $rates[] = $this->dataObjectProcessor->buildOutputDataArray(
                    $rateData,
                    'MageWorx\ShippingRules\Api\Data\RateInterface'
                );
            }
        }
        $searchResults->setItems($rates);

        return $searchResults;
    }

    /**
     * Delete Rate by given Rate Identity
     *
     * @param string $rateId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($rateId)
    {
        return $this->delete($this->getById($rateId));
    }

    /**
     * Delete Rate
     *
     * @param Rate $rate
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Rate $rate)
    {
        try {
            $this->resource->delete($rate);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the rate: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * Load Rate data by given Rate Identity
     *
     * @param string $rateId
     * @return Rate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($rateId)
    {
        /** @var Rate $rate */
        $rate = $this->rateFactory->create();
        $this->resource->load($rate, $rateId);
        if (!$rate->getId()) {
            throw new NoSuchEntityException(__('Rate with id "%1" does not exist.', $rateId));
        }

        return $rate;
    }

    /**
     * Get empty Rate
     *
     * @return Rate|RateInterface
     */
    public function getEmptyEntity()
    {
        /** @var Rate $rate */
        $rate = $this->rateFactory->create();

        return $rate;
    }
}
