<?php

namespace Acidgreen\SploshExo\Model\Import;

use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\Exo\Model\Import\AbstractImporter;
use Acidgreen\SploshExo\Model\StaffFactory as StaffModelFactory;
use Acidgreen\Exo\Model\Process;
use Acidgreen\SploshExo\Helper\Staff as StaffHelper;
use Magento\Store\Model\StoreManagerInterface;

use Psr\Log\LoggerInterface;

class Staff extends AbstractImporter
{
	/**
	 * @var ApiHelper
	 */
	protected $apiHelper;
	
	/**
	 * @var ConfigHelper
	 */
	protected $configHelper;
	
	/**
	 * @var StaffModelFactory
	 */
	protected $staffFactory;
	
	/**
	 * @var array
	 */
	protected $importData;

    /**
     * @var Process
     */
    protected $process;
    
    /**
     * @var StaffHelper
     */
    protected $staffHelper;
    
    /**
     * @var array
     */
    protected $staffs;
    
	/**
	 * @var StoreManagerInterface
	 */
    protected $storeManager;
    
    /**
     * @var int
     */
    protected $currentWebsiteId;
    
	/**
	 * @var LoggerInterface 
	 */
	protected $logger;
	
	public function __construct(
		ApiHelper $apiHelper,
		ConfigHelper $configHelper,
		StaffModelFactory $staffFactory,
		StaffHelper $staffHelper,
		StoreManagerInterface $storeManager,
		LoggerInterface $logger
	) {
		$this->apiHelper = $apiHelper;
		$this->configHelper = $configHelper;
		$this->staffFactory = $staffFactory;
		$this->staffHelper = $staffHelper;
		
		$this->storeManager = $storeManager;
		
		$this->logger = $logger;
		
	}
	
    public function initImport($process)
	{
        $this->logger->debug(__METHOD__);
        
        /**
         * SPL-335 - Make sure sync is working under the proper website scope
         */
        $this->process = $process;
        $this->currentWebsiteId = $this->storeManager->getWebsite($this->process->getWebsites())->getId();
        $this->configHelper->unsetExoCurrentWebsite();
        $this->configHelper->setExoCurrentWebsite($this->process->getWebsites());
		$this->initStaffCollection();
        
        
        parent::initImport($process);
	}
	
	protected function getImportData()
	{
	    // get import data here	
        $data = [];

        $page = 1;
        $params = ['page' => $page, 'pagesize' => 100];
        $tempStaffCount = 1;

        $tempData = [];
        while ($tempStaffCount > 0) {
            $tempStaffCount = 0;
            $params['page'] = $page;
            $response = $this->apiHelper->getAllStaff($params);
            
            if (empty($response['error_status'])) {
                $body = \GuzzleHttp\Ring\Core::body($response);
                
                // $data = json_decode($body, true);
                $tempData = json_decode($body, true);
                $tempStaffCount = count($tempData);
                $data = array_merge($data, $tempData);
                $this->logger->debug(__('%1 :: FETCHED %2 RESULTS ON page %3', __METHOD__, $tempStaffCount, $page));
                $this->logger->debug(__('%1 :: Total data count = %2', __METHOD__, count($data)));
            }


            $page++;
        }

        
        return $data;
	}
	
	/**
	 * 
	 */
	protected function startImport()
	{
		try {
			
			$this->process->setStatus(Process::STATUS_PROCESSING)->save();
			$staffData = $this->importData;
			
			// processImport
			$this->processImport($staffData);
			$this->process->setStatus(Process::STATUS_COMPLETED)->save();
			// set status to completed if OK?
		} catch (\Exception $e) {
			// report it ASAP?
			$this->logger->debug('startImport :: ERROR :: '.$e->getMessage());
			$this->logger->debug($e->getTraceAsString());
			$this->process->setStatus(Process::STATUS_ERROR)->save();
		}
	}
	
	/**
	 * 
	 */
	protected function finishImport()
	{
		
	}
	
	protected function processImport($staffDataArray)
	{
		// load staff data collection
		// update those existing
		// create for new staff...
		// no delete OK?
		$this->logger->debug(__METHOD__.' :: staffDataArray count :: '.count($staffDataArray));
		sleep(5);
		
		$exoStaffFields = $this->staffHelper->getExoStaffFields();
		
		foreach ($staffDataArray as $staffData) {
			try {

				if (isset($this->staffs[$staffData['id']])) {
					$staff = $this->staffs[$staffData['id']];
				} else {
					$staff = $this->staffFactory->create();
				}
				
				
				// $staff->setName($staffData['name']);
				// $staff->setJobtitle($staffData['jobtitle']);
                $staff->setExoStaffId($staffData['id']);
                
                $staffDataExtraFields = $staffData['extrafields'];
                foreach ($staffDataExtraFields as $otherStaffData) {
                    // @todo: ISACTIVE mapping is weird?
                	if (isset($exoStaffFields[$otherStaffData['key']])) {
                        // @todo: ISACTIVE mapping is weird?
                        if ($exoStaffFields[$otherStaffData['key']] == 'is_active') 
                            $otherStaffData['value'] = $this->getIsActiveValue($otherStaffData['value']);

                		$staff->setData($exoStaffFields[$otherStaffData['key']], $otherStaffData['value']);
                		
                		$staff->setWebsiteId($this->currentWebsiteId);
                	}
                }
                
				$staff->save();
					
				$this->logger->debug(__('%1 :: Staff data saved. ID: %2', __METHOD__, $staff->getId()));
			} catch (\Exception $e) {
				$this->logger->debug(__('%1 :: ERROR ENCOUNTERED :: %2', __METHOD__, $e->getMessage()));
				$this->logger->debug($e->getTraceAsString());
			}
		}
	}
	
	private function initStaffCollection()
	{
		$staffs = [];
		
        /**
         * SPL-335 - Make sure sync is working under the proper website scope
         */
		$collection = $this->staffFactory->create()->getCollection();
        if (!empty($this->currentWebsiteId)) {
            $collection->addFieldToFilter('website_id', $this->currentWebsiteId);
        }
		
		if (count($collection)) {
			/** @var \Acidgreen\SploshExo\Model\Staff $staff */
			foreach ($collection as $staff) {
				$staffs[$staff->getExoStaffId()] = $staff;
			}
		}
		$this->staffs = $staffs;
	}

    private function getIsActiveValue($isActive)
    {
        return ($isActive == 'Y' || $isActive == '1');
    }
}
