<?php

namespace Acidgreen\SploshExo\Helper\Records;

use Magento\Framework\App\Helper\AbstractHelper;
use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\SploshExo\Helper\Api\Config as ConfigHelper;
use Acidgreen\Exo\Model\Records\Tosync\CustomerCreate as ExoCustomerCreate;
use Acidgreen\Exo\Model\Records\Tosync\OrderCreate as ExoOrderCreate;
use Acidgreen\Exo\Model\Records\Tosync\ProcessTypeInterface;
use Acidgreen\Exo\Model\Records\Tosync\Ids as RecordsToSyncIds;
use Acidgreen\Exo\Model\Records\Tosync as RecordsToSync;
use Acidgreen\Exo\Model\Records\Tosync\IdsFactory as RecordsToSyncIdsFactory;
use Acidgreen\Exo\Model\ResourceModel\Records\Tosync as ResyncResourceModel;
use Psr\Log\LoggerInterface;

class Tosync extends AbstractHelper
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var ConfigHelper
	 */
	protected $configHelper;

	/**
	 * @var ApiHelper
	 */
	protected $apiHelper;

	/**
	 * @var ExoCustomerCreate
	 */
	protected $exoCustomerCreateModel;

	/**
	 * @var ProcessTypeInterface
	 */
	protected $processTypeModel;

	/**
	 * @var ExoOrderCreate
	 */
	protected $exoOrderCreateModel;

	/**
	 * @var RecordsToSyncIdsFactory
	 */
	protected $recordsTosyncIdsFactory;

	/**
	 * @var ResyncResourceModel\CollectionFactory
	 */
	protected $resyncRequestCollection;
	/**
	 *
	 * @param ApiHelper $apiHelper
	 * @param ConfigHelper $configHelper
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		ApiHelper $apiHelper,
		ConfigHelper $configHelper,
		ExoCustomerCreate $exoCustomerCreateModel,
		ExoOrderCreate $exoOrderCreateModel,
		RecordsToSyncIdsFactory $recordsTosyncIdsFactory,
		ResyncResourceModel\CollectionFactory $resyncRequestCollection,
		LoggerInterface $logger
	) {
		$this->apiHelper = $apiHelper;
		$this->configHelper = $configHelper;

		$this->exoCustomerCreateModel = $exoCustomerCreateModel;
		$this->exoOrderCreateModel = $exoOrderCreateModel;

		$this->recordsTosyncIdsFactory = $recordsTosyncIdsFactory;

		$this->resyncRequestCollection =$resyncRequestCollection;
		$this->logger = $logger;
	}

	/**
	 * Determine what process type model to use
	 * @param RecordsToSync $resyncRequest
	 * @return \Acidgreen\Exo\Model\Records\Tosync\ProcessTypeInterface|boolean
	 */
	public function getProcessTypeModel(RecordsToSync $resyncRequest)
	{
		// get related IDs to the request
		/*
		$collection = $this->recordsTosyncIdsFactory->create()->getCollection();
		$collection->addFieldToFilter('process_type_id', $resyncRequest->getId());

		if ($collection->count() > 0) {

		}
		*/
		switch ($resyncRequest->getProcessType()) {
			case RecordsToSync::PROCESS_TYPE_CUSTOMER_CREATE:
				$this->processTypeModel = $this->exoCustomerCreateModel;
				break;
			case RecordsToSync::PROCESS_TYPE_ORDER_CREATE:
				$this->processTypeModel = $this->exoOrderCreateModel;
				break;
			default:
				break;
		}

		if (isset($this->processTypeModel))
			return $this->processTypeModel;

		return false;


	}

	/**
	 * Queue for resync
	 * @param mixed $magentoId
	 * @param string $website
	 * @param string $processType
	 */
    public function queueForSync($magentoId, $website, $processType)
    {
    	// get resyncRequest to use
    	$resyncRequest = $this->getResyncRequest($website, $processType);
    	if ($resyncRequest) {

    		// get exo_records_tosync_ids instance here
    		$resyncIdsModel = $this->getResyncIdsModel($website, $resyncRequest->getId());
    		$magentoIds = $resyncIdsModel->getMagentoIds();

    		// add magentoId to queue
    		if (!empty($magentoIds) && !preg_match("/$magentoId/", $magentoIds)) {
    			$magentoIds .= ',' . $magentoId;
    		} else if (empty($magentoIds)) {
    			$magentoIds .= $magentoId;
    		}

    		// save changes
    		$resyncIdsModel->setProcessTypeId($resyncRequest->getId())
    					->setWebsites($website)
    					->setMagentoIds($magentoIds)
    					->setStatus(RecordsToSync::STATUS_PENDING)
    					->save();
    		$resyncRequest->setStatus(RecordsToSync::STATUS_PENDING)->save();

    	}
    }

    private function getResyncRequest($website, $processType)
    {
    	$collection = $this->resyncRequestCollection->create();
    	$collection->addFieldToFilter('process_type', $processType);
    	$collection->addFieldToFilter('website', $website);

    	if ($collection->count() > 0)
    		return $collection->getFirstItem();

    	return false;

    }

    private function getResyncIdsModel($website, $processTypeId)
    {
    	$resyncIdsModel = $this->recordsTosyncIdsFactory->create();
    	$collection = $resyncIdsModel->getCollection();
    	$collection->addFieldToFilter('process_type_id', $processTypeId);
    	$collection->addFieldToFilter('websites', $website);
    	$collection->addFieldToFilter('status', RecordsToSync::STATUS_PENDING);

    	if ($collection->count() > 0) {
    		$resyncIdsModel = $collection->getFirstItem();
    	}

    	return $resyncIdsModel;
    }
}
