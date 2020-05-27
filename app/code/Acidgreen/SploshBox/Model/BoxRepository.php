<?php


namespace Acidgreen\SploshBox\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SortOrder;
use Acidgreen\SploshBox\Model\ResourceModel\Box as ResourceBox;
use Magento\Framework\Reflection\DataObjectProcessor;
use Acidgreen\SploshBox\Api\BoxRepositoryInterface;
use Acidgreen\SploshBox\Model\ResourceModel\Box\CollectionFactory as BoxCollectionFactory;
use Acidgreen\SploshBox\Api\Data\BoxSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Acidgreen\SploshBox\Api\Data\BoxInterfaceFactory;
use Psr\Log\LoggerInterface;

class BoxRepository implements BoxRepositoryInterface
{

    protected $dataObjectProcessor;

    protected $resource;

    protected $dataBoxFactory;

    private $storeManager;

    protected $dataObjectHelper;

    protected $boxCollectionFactory;

    protected $boxFactory;

    protected $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
	protected $logger;
    /**
     * @param ResourceBox $resource
     * @param BoxFactory $boxFactory
     * @param BoxInterfaceFactory $dataBoxFactory
     * @param BoxCollectionFactory $boxCollectionFactory
     * @param BoxSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceBox $resource,
        BoxFactory $boxFactory,
        BoxInterfaceFactory $dataBoxFactory,
        BoxCollectionFactory $boxCollectionFactory,
        BoxSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
    	LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->boxFactory = $boxFactory;
        $this->boxCollectionFactory = $boxCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBoxFactory = $dataBoxFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Acidgreen\SploshBox\Api\Data\BoxInterface $box
    ) {
        /* if (empty($box->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $box->setStoreId($storeId);
        } */
        try {
            $this->resource->save($box);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the box: %1',
                $exception->getMessage()
            ));
        }
        return $box;
    }
    
    public function getBoxByType($params)
    {
    	
    	$type = strtoupper($params['box_type']);
    	
    	$box = $this->boxFactory->create();
    	$boxes = $box->getCollection();
    	$boxes->addFieldToFilter('box_type', ['eq' => $type]);
    	// $boxes->addFieldToFilter('website_id', ['eq' => $params['website_id']]);
    	
    	// if ($boxes->count() > 0) {
    	if (count($boxes) > 0) {
    		$box = $boxes->getFirstItem();
    		
    		return $box;
    	}
    	
    	return false;
    }
    
    public function create()
    {
    	return $this->boxFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($boxId)
    {
        $box = $this->boxFactory->create();
        $box->load($boxId);
        if (!$box->getId()) {
            throw new NoSuchEntityException(__('box with id "%1" does not exist.', $boxId));
        }
        return $box;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $collection = $this->boxCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
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
        $items = [];
        
        foreach ($collection as $boxModel) {
            $boxData = $this->dataBoxFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $boxData,
                $boxModel->getData(),
                'Acidgreen\SploshBox\Api\Data\BoxInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $boxData,
                'Acidgreen\SploshBox\Api\Data\BoxInterface'
            );
        }
        $searchResults->setItems($items);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Acidgreen\SploshBox\Api\Data\BoxInterface $box
    ) {
        try {
            $this->resource->delete($box);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the box: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($boxId)
    {
        return $this->delete($this->getById($boxId));
    }
}
