<?php

namespace Acidgreen\SploshExo\Observer\Exo;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Acidgreen\SploshExo\Helper\Staff as StaffHelper;
use Psr\Log\LoggerInterface;

class SyncStaff implements ObserverInterface
{
	/**
	 * @var StaffHelper
	 */
	protected $staffHelper;
	
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		StaffHelper $staffHelper,
		LoggerInterface $logger
	) {
		$this->staffHelper = $staffHelper;
		$this->logger = $logger;
	}
	
    public function execute(Observer $observer) 
    {
		
        /** @var \Acidgreen\SploshExo\Model\Staff $staff */
		$staff = $observer->getStaff();
		
		$exoStaffData = $this->staffHelper->parseStaffUpdate($staff);

		$response = $this->staffHelper->processStaffUpdate($exoStaffData);
		
		if ($response['status'] == '200') {
			
			$body = \GuzzleHttp\Ring\Core::body($response);
			
			$this->logger->debug(__METHOD__.' :: ATTENTION :: response body');
			$this->logger->debug(print_r($body, true));
			
		} else {
			$this->logger->debug(__METHOD__.' :: ISSUE with PUT API call to EXO');
			$this->logger->debug(print_r($response, true));
		}
		
		return;
	}
}
