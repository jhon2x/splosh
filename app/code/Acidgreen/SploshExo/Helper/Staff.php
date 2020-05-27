<?php

namespace Acidgreen\SploshExo\Helper;

use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\SploshExo\Helper\Api\Config as ConfigHelper;
use Acidgreen\SploshExo\Model\Staff as StaffModel;
use Psr\Log\LoggerInterface;

class Staff
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
	 * @var array
	 */
	private $exoStaffFields;
	
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		ApiHelper $apiHelper,
		ConfigHelper $configHelper,
		LoggerInterface $logger
	) {
		$this->apiHelper = $apiHelper;
		$this->configHelper = $configHelper;
		$this->logger = $logger;
		
		$this->initExoStaffFields();
	}
	
	public function parseStaffUpdate(StaffModel $staff)
	{
		$exoStaffData = [];
		
		if (!empty($staff)) {
			
			$exoStaffData = [
				'id' => $staff->getExoStaffId(),
				'extrafields' => []
			];
			
			foreach ($this->exoStaffFields as $exoField => $magentoField) {
				$extrafield = [
					'key' => $exoField,
					'value' => $staff->getData($magentoField)
				];
				$exoStaffData['extrafields'][] = $extrafield;
			}
		}
		
		$this->logger->debug(__METHOD__.' :: exoStaffData :: '.print_r($exoStaffData, true));
		
		return $exoStaffData;
	}
	
	public function processStaffUpdate($exoStaffData)
	{
		$response = false;
		
		if (!empty($exoStaffData))
			$response = $this->apiHelper->sendStaffUpdate($exoStaffData);
		
		return $response;
	}
	
	private function initExoStaffFields()
	{

		$exoStaffFields = [
				'STAFFNO' => 'exo_staff_id',
				'NAME' => 'name',
				'NICKNAME' => 'nickname',
				'JOBTITLE' => 'jobtitle',
				'PHONE' => 'phone_no',
				'ISACTIVE' => 'is_active',
				'EMAIL_ADDRESS' => 'email'
		];
		$this->exoStaffFields = $exoStaffFields;
	}
	
	public function getExoStaffFields()
	{
		return $this->exoStaffFields;
	}
}
