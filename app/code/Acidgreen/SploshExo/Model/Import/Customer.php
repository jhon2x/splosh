<?php

namespace Acidgreen\SploshExo\Model\Import;


use Magento\ImportExport\Model\ImportFactory;
use Acidgreen\Exo\Model\Import\AbstractImporter;
use Acidgreen\Exo\Model\ArrayAdapterFactory;
use Acidgreen\Exo\Model\ProcessFactory;
use Acidgreen\Exo\Helper\Data as HelperClass;
use Acidgreen\Exo\Helper\ImportError as ErrorHelper;
use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\SploshExo\Helper\Api\Config as ConfigHelper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Indexer\Model\IndexerFactory;
use \Acidgreen\Exo\Model\ExistingCustomer;
use \Acidgreen\Exo\Helper\CustomerInterface as CustomerHelper;
use Acidgreen\SploshExo\Model\Config\Source\Staff as ExoStaff;
use Magento\Customer\Model\Customer as CustomerModel;

class Customer extends AbstractImporter
{

	/**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $importModelFactory;

    /**
     * @var \Acidgreen\Exo\Model\ArrayAdapterFactory
     */
    protected $arrayAdapterFactory;

    /**
     * @var \Acidgreen\Exo\Helper\ImportError
     */
    protected $errorHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
	protected $logger;

	/**
     * @var \Acidgreen\Exo\Model\ProcessFactory
     */
    protected $processFactory;

    /**
     * @var \Acidgreen\Exo\Model\Process
     */
    protected $process;

    /**
     * @var Array
     */
    protected $importData;

	/**
     * @var \Acidgreen\Exo\Helper\Data
     */
	protected $helper;

	/**
     * @var \Acidgreen\Exo\Helper\Api\Api
     */
	protected $apiHelper;

	/**
     * @var \Acidgreen\Exo\Helper\Api\Config
     */
	protected $configHelper;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $logTrace = "";

    /**
     * @var
     */
    protected $errorMessages;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    /********** SPL-80 **********/
    /**
     *
     * @var \Acidgreen\Exo\Model\ExistingCustomer
     */
    protected $existingCustomers;

    /**
     *
     * @var array
     */
    protected $existingCustomersArray;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    /********** SPL-80 **********/

    protected $_staff;

    /**
     * SPL-440?
     * @var array
     */
    protected $exoCustomersAddressToDelete;

    /**
    * Customer Import Contructor
    *
    * @param ImportFactory $importModelFactory
    * @param ArrayAdapterFactory $arrayAdapterFactory
    * @param ErrorHelper $errorHelper
    * @param ProcessFactory $processFactory
    * @param HelperClass $helper
    * @param ApiHelper $apiHelper
    * @param Logger @logger
    * @param IndexerFactory $indexerFactory
    */
	public function __construct(
		ImportFactory $importModelFactory,
		ArrayAdapterFactory $arrayAdapterFactory,
		ErrorHelper $errorHelper,
		ProcessFactory $processFactory,
		HelperClass $helper,
		ApiHelper $apiHelper,
		ConfigHelper $configHelper,
		Logger $logger,
        IndexerFactory $indexerFactory,
		// SPL-80
		ExistingCustomer $existingCustomers,
		CustomerHelper $customerHelper,
    ExoStaff $staff,
		CustomerModel $customerModel,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	) {


		$this->importModelFactory 	= $importModelFactory;
		$this->arrayAdapterFactory 	= $arrayAdapterFactory;
		$this->errorHelper 			= $errorHelper;
		$this->processFactory 		= $processFactory;
		$this->helper 				= $helper;
		$this->apiHelper 			= $apiHelper;
		$this->configHelper 		= $configHelper;
		$this->logger 				= $logger;
        $this->indexerFactory       = $indexerFactory;

		$this->existingCustomers	= $existingCustomers;
		$this->customerHelper		= $customerHelper;

		$this->settings = [
			'entity'                    => $this->configHelper->getEntity('customer'),
			'behavior'                  => $this->configHelper->getBehavior('customer'),
			'validation_strategy'       => $this->configHelper->getValidationStrategy(),
			'allowed_error_count'       => $this->configHelper->getAllowedErrorCount(),
			'import_images_file_dir'    => $this->configHelper->getImportFileDir(),
		];

		$this->_staff = $staff;
		$this->customerModel = $customerModel;
		$this->_storeManager = $storeManager;

		$this->rangeRestrictionSet = array();
		$this->websiteUniqueId = 0;
	}

	/**
	 * Initialize Import
	 * @param \Acidgreen\Exo\Model\Process $process
	 */
	public function initImport($process)
	{
		$this->logger->debug('Init Customer Import');

		$this->process = $process;

		// SPL-64 - Unset first before set
		$this->configHelper->unsetExoCurrentWebsite();
		$this->configHelper->setExoCurrentWebsite($this->process->getWebsites());

		// SPL-??
		$this->existingCustomersArray = $this->existingCustomers->getCustomerArray();

		parent::initImport($process);
	}

	/**
     * Get Import Data
     *
     * @return Array $data
     */
	protected function getImportData()
	{

		$data = ['customers' => []];

		$page = 1;
		// for debugging purposes if $page == $PAGE_LIMIT break etc...
		$PAGE_LIMIT = 3;

		$pagesize = $this->configHelper->getApiPagesize();


		$tempCustomersArrayCount = 1;
		$ctr = 1;
		while ($tempCustomersArrayCount > 0) {
			// we're setting this to 0 to prevent an endless loop
			$tempCustomersArrayCount = 0;
			$customerResponse = $this->apiHelper->getAllActiveCustomers([
				'page' => $page,
				'pagesize' => $pagesize
			]);

			if($customerResponse['status'] == '200') {

				$body = \GuzzleHttp\Ring\Core::body($customerResponse);

				$customerData = json_decode($body, true);

				$filteredCustomerData = [];
				$tempCustomersArrayCount = count($customerData);

				$this->logger->debug(__('%1 :: PAGE = %2, ROW COUNT = %3', __METHOD__, $page, $tempCustomersArrayCount));

				foreach ($customerData as $c) {
					// CONVERT EMAIL TO LOWERCASE AS THE MAGENTO IMPORT BREAKS

					// $c['email'] = $this->getExoEmail($c['email']);
					$c['email'] = $this->customerHelper->getExoEmail($c);

					if (filter_var($c['email'], FILTER_VALIDATE_EMAIL) 
						&& $this->customerHelper->isEmailValid($c['email'])) {
						// keyed by email even if there are multiple occurences of the email
						// to prevent email unique constraint error

						$filteredCustomerData[$c['email']] = $c;
					}
					$ctr++;
				}

				$data['customers'] = array_merge($data['customers'], $filteredCustomerData);

            } else {
                $this->logger->debug(__('%1 :: STOP SYNC, No records found for this page: %2', __METHOD__, $page));
                /*
            	$this->logger->debug(__('%1 :: WARNING :: Not all customer records can be synced due to this %2 issue.', __METHOD__, $customerResponse['status']));
                 */
                $this->logger->debug(print_r($customerResponse, true));
                break;
            }

			$page++;
            // add 1-3 second delay for QPS issues
            sleep(3);

		}

		// REMOVE DUPLICATING EMAILS PER PAGE
		$filteredCustomers = [];
		foreach ($data['customers'] as $email => $c) {
			$filteredCustomers[$email] = $c;
		}

		$data['customers'] = [];
		foreach ($filteredCustomers as $email => $c) {
			$data['customers'][] = $c;
		}

		$this->logger->debug('getImportData NUMBER OF CUSTOMERS SUBJECT FOR IMPORT AFTER REMOVING DUPLICATE EMAILS BETWEEN PAGES '.count($data['customers']));

		return $data;
	}

	/**
     * Start Import
     */
	protected function startImport()
	{
		$this->logger->debug('Start Customer Import');

		try {

			$customersArray = $this->getCustomerData();

			if(empty($customersArray)) {
				$this->logger->debug('No Customer Data!');

				// set last run date?
				$lastRunDate = new \DateTime();
				$this->process
					->setStatus(\Acidgreen\Exo\Model\Process::STATUS_COMPLETED)
					->setLastRunDate($lastRunDate->format('Y-m-d H:i:s'))
					->save();

				return;
	 		} else {
				$this->exoCustomersAddressToDelete = [];
				foreach ($customersArray as $c) {
					if (!empty($c['exo_customer_id'])) {
						$this->exoCustomersAddressToDelete[] = $c['exo_customer_id'];
					}
				}
	 		}

	 		$customersArrays = array_chunk($customersArray, 2000);

			foreach($customersArrays as $customers){
			    $this->processImport($customers);
            }

			$this->purgeDuplicateAddresses();

			$this->process->setStatus(\Acidgreen\Exo\Model\Process::STATUS_COMPLETED)->save();

		} catch (\Exception $e) {
			$this->process->setStatus(\Acidgreen\Exo\Model\Process::STATUS_ERROR)->save();

			$this->logger->debug('Error Import DUMP ::'.print_r($e->getTraceAsString(), true));
			$this->logger->debug('Error Import'.$e->getMessage());

		}

		$this->logger->debug('Log Trace '.print_r($this->getLogTrace(), true));

		$this->logger->debug('Error Messages '.print_r($this->getErrorMessages(), true));

		//get column mapping
	}

	/**
	* Customer Data Mapping
	* @todo fix address duplicates...
	* @return Array @customers
	*/
	protected function getCustomerData()
	{
		$rawData = $this->importData;

		$customers = [];
		$staffData = $this->_staff->getScalarData();

		foreach($this->_storeManager->getWebsites() as $website) {
			if ($website->getCode() == $this->configHelper->getExoCurrentWebsiteId()) {
				$this->websiteUniqueId = $website->getId();
				$this->logger->debug("Website unique ID is ".$this->websiteUniqueId);
				break;
			}
		}

		foreach($rawData['customers'] as $exoCustomerData) {

			if(!trim($exoCustomerData['email'])) {
				$this->logger->debug('No Email ');
				continue;
			}

			if (!filter_var($exoCustomerData['email'], FILTER_VALIDATE_EMAIL)) {
				$this->logger->debug('Invalid email format ' . $exoCustomerData['email']);
				continue;
			}

			if($exoCustomerData['id'] == '@' || !$exoCustomerData['id']) {
				$this->logger->debug('SPL Customer Sync :: EXO ID is "@" or invalid...');
				continue;
			}

			if (!$this->customerHelper->isValidForImport($exoCustomerData)) {
				$this->logger->debug('SPL Customer Sync :: Customer not valid for import. EXO Customer ID :: '.print_r([$exoCustomerData['id']], true));
				continue;
			}


            $customerMappedData = $this->mapCustomerColumns($exoCustomerData);


            // found issues in pagination, merging the results of pagination...
            $currentWebsite = $this->configHelper->getExoCurrentWebsiteId();

            $displaySalesRep = $exoCustomerData['extrafields'][20]['value'];
            if ($displaySalesRep == 'Y') {
                $displaySalesRep = 'yes';
            } else {
                $displaySalesRep = 'no';
            }

			$customer = [
				'email'					=> trim($exoCustomerData['email']),
				'exo_customer_id'		=> $exoCustomerData['id'],
                'exo_alpha_code'        => $exoCustomerData['alphacode'],
                '_website'				=> $currentWebsite,
				'group_id'				=> $this->customerHelper->getDebtorB2cMagentoGroupId($currentWebsite, $exoCustomerData),
				'_address_country_id'			=> $this->getCountryForCustomer(),
                /**
                 * SPL-335 - Remove salesperson mapping here as it causes issues on NZ B2B sync
                 */
                'display_salesrep' => $displaySalesRep,
				'exo_balance' => $exoCustomerData['balance'],
				// password_hash => existingPasswordHash instead?
			];

            /**
             * SPL-335 - Move salesperson mapping here as it causes issues on NZ B2B sync
             */
            if (!empty($staffData[$exoCustomerData['salespersonid']])) {
                $customer['salesperson'] = mb_strtolower($staffData[$exoCustomerData['salespersonid']]);
            }
			
			/**
			 * SPL-319
			 * Process only category_restrictions if B2B sync
			 * @var string $b2bWebsiteCodes
			 */
			$b2bWebsiteCodes = $this->configHelper->getScopeConfigWebsite(
				\Acidgreen\SploshExo\Helper\Api\Config::CONFIG_B2B_WEBSITE_CODES
			);
			$isWebsiteB2B = preg_match("/".$this->process->getWebsites()."/", $b2bWebsiteCodes);
			if ($isWebsiteB2B) {
                if (!empty($exoCustomerData['extrafields'][18]['value'])) {
                    $customer['category_restrictions'] = $exoCustomerData['extrafields'][18]['value']; //included words
                }
            }

			//Range Restrictions fix
			try {
				$loadedCustomer = $this->customerModel->setWebsiteId($this->websiteUniqueId)->loadByEmail($customer['email']);

				if ($loadedCustomer->getId()) {
					$loadedCustomer->setRangeRestrictions($exoCustomerData['extrafields'][19]['value'])->save();
				} else {
					$this->rangeRestrictionSet[] = array(
						'email' => $customer['email'],
						'restriction' => $exoCustomerData['extrafields'][19]['value']
					);
				}
			} catch (\Exception $e) {
				$this->rangeRestrictionSet[] = array(
					'email' => $customer['email'],
					'restriction' => $exoCustomerData['extrafields'][19]['value']
				);
				$this->logger->debug($customer['email'] . " cannot be loaded, queued for restriction");
			}

			// for filtering purposes
			$customerMappedData['_address_default_shipping_'] = '1';
            // _address_street for shipping
            $customerMappedData['_address_street'] = $this->getAddressStreet($exoCustomerData, 'shipping');

			// billing gets replaced, add a new element to $customers[] instead?
			$billingAddressMapping = $this->getCustomerBillingAddressColumns();
			$customerBillingMappedData = $this->mapCustomerColumns($exoCustomerData, $billingAddressMapping);
            $customerBillingMappedData['_address_street'] = $this->getAddressStreet($exoCustomerData, 'billing');

			// SPL-80 - set password and addresses if new customer
			if (isset($this->existingCustomersArray[$exoCustomerData['id']])) {
				$existingCustomer = $this->getExistingCustomer($exoCustomerData['id']);

                // temporary
                if (($existingCustomer->getFirstname() == $existingCustomer->getPasswordHash())
                || ($existingCustomer->getLastname() == $existingCustomer->getPasswordHash())) {
                    $customer['password_hash'] = $this->customerHelper->getPassword($exoCustomerData);
                } else {
                    // else, if isAuthenticated and is B2B, use existing hash..if not get the current password from EXO
                     $customer['password_hash'] = $existingCustomer->getPasswordHash();
//                    $customer['password_hash'] = $this->customerHelper->getPasswordForExistingUser($exoCustomerData, $existingCustomer);
                }

			} else {
				$customer['password_hash'] = $this->customerHelper->getPassword($exoCustomerData);
			}

            // row entered sometimes(?) is 3 rows causing invalid data...
			// $customers[] = array_merge($customer, $customerMappedData);
			$customer = array_merge($customer, $customerMappedData);
			//SPL-489: temporarily use delivery address as default billing address
			$customer = array_merge($customer, ['_address_default_billing_' => '1']);
            // exclusive-client only mappings...
            $exclusiveMappings = $this->customerHelper->mapExclusiveCustomerColumns($exoCustomerData);

            $customers[] = array_merge($customer, $exclusiveMappings);

//			if (
//				!isset($existingCustomer) ||
//				!$this->customerDefaultAddressExists(
//					$existingCustomer,
//					'getDefaultBillingAddress',
//					$customerBillingMappedData
//				)
//			) {
//				// add new customer import row for billing_address
//				$customerBilling = [
//				    \Magento\CustomerImportExport\Model\Import\Customer::COLUMN_EMAIL => $customer['email'],
//                    \Magento\CustomerImportExport\Model\Import\Customer::COLUMN_WEBSITE => $customer['_website'],
//					'_address_country_id' => $this->getCountryForCustomer(),
//					'_address_default_billing_' => '1',
//					'_address_default_shipping_' => '0'
//				];
//
//                // $customerBilling = array_merge($customer, $customerBilling);
//				$customerBilling = array_merge($customerBilling,  $customerBillingMappedData);
//                // $customerBilling = array_merge($customerBilling, $exclusiveMappings);
//				$customers[] = $customerBilling;
//
//			}

		}

		return $customers;
	}

	protected function customerDefaultAddressExists(
		\Magento\Customer\Model\Customer $existingCustomer,
		$getter,
		array $addressData
	) {
		// compare the following - street, city, postcode, region, country_id
		$existingAddress = $existingCustomer->$getter();
		if (isset($existingAddress) && is_object($existingAddress)) {

			$existingAddress = $existingCustomer->$getter()->getData();
			// if if here...
            if (isset($existingAddress['street']) && isset($existingAddress['city'])) {
                if (
                    (strtolower($existingAddress['street']) === strtolower($addressData['_address_street'])) &&
                    (strtolower($existingAddress['city']) === strtolower($addressData['_address_city']))
                ) {
                    return true;
                }
            }
		}

		return false;
	}

    /**
     * Validate First Name
     *
     * @param Array $exoCustomerData
     * @return String $firstName
     **/
    protected function validateFirstName($exoCustomerData)
    {
    	if(isset($exoCustomerData['defaultcontact']['firstname']) && trim($exoCustomerData['defaultcontact']['firstname'])) {
    		$firstName = $exoCustomerData['defaultcontact']['firstname'];
    	} else {
    		$firstName = 'NA';
    	}

    	return $firstName;
    }

    /**
     * Validate Last Name
     *
     * @param Array $exoCustomerData
     * @return String $lastName
     **/
    protected function validateLastName($exoCustomerData)
    {
    	if(isset($exoCustomerData['defaultcontact']['lastname']) && trim($exoCustomerData['defaultcontact']['lastname'])) {
    		$lastName = $exoCustomerData['defaultcontact']['lastname'];
    	} else {
    		$lastName = 'NA';
    	}

    	return $lastName;
    }

    /**
     * Map Magento Customer Data
     *
     * @param Array $exoCustomerData
     * @return Array $data
     **/

    protected function mapCustomerColumns($exoCustomerData, $customerMapping = [])
    {
    	if (empty($customerMapping))
        	$customerMapping = $this->configHelper->getCustomerMappingConfig();

        $data = array();

        foreach($customerMapping as $mapKey => $mapValue) {

            if(strpos($mapKey, 'billing') !== FALSE) {
                $mapKey = str_replace('billing', '', $mapKey);
            } else if (strpos($mapKey, 'shipping') !== FALSE) {
                $mapKey = str_replace('shipping', '', $mapKey);
            }
            $data[$mapKey] = $this->getCustomerExoValue($exoCustomerData, $mapValue);

        }

        return $data;
    }

    /**
     * Get mappings related to billing address
     * @return array
     */
    protected function getCustomerBillingAddressColumns()
    {
    	$customerMapping = $this->configHelper->getCustomerMappingConfig();

    	$customerMapping = array_filter($customerMapping, function($k){
    		return preg_match("/billing/", $k);
    	}, ARRAY_FILTER_USE_KEY);
    	return $customerMapping;
    }

    /**
     * Get Customer using exo_customer_id as key
     * @param string $key
     * @return \Magento\Customer\Model\Customer $customer
     */
    private function getExistingCustomer($key)
    {
    	$customer = $this->existingCustomersArray[$key];

    	return $customer;
    }


    /**
     * Get EXO Data Value
     *
     * @param Array $exoCustomerData
     * @param String $mapValue
     * @return String
     **/
    protected function getCustomerExoValue($exoCustomerData, $mapValue)
    {

    	$data = 'NA';
        if(strpos($mapValue, ':') !== FALSE) {
            $mapDataArray = explode(':', $mapValue);

            if(isset($exoCustomerData[$mapDataArray[0]][$mapDataArray[1]])) {

            	$data = $this->getEmptyDefaultValue($mapDataArray[1]);
            	if (!empty(trim($exoCustomerData[$mapDataArray[0]][$mapDataArray[1]]))) {
            		$data = $exoCustomerData[$mapDataArray[0]][$mapDataArray[1]];
            	}

            } else {
                $data = 'NA';
            }

        } else {

            if(!empty($exoCustomerData[$mapValue])) {

                $data = $this->getEmptyDefaultValue($mapValue);
                if (!empty(trim($exoCustomerData[$mapValue]))) {
                    $data = $exoCustomerData[$mapValue];
                }

            } else {
                $data = 'NA';
            }
        }


        return $data;
    }

    /**
     * Get Default Field Value
     *
     * @todo Needs to be on admin
     * @param String $field
     * @param String $defaultValue
     *
     **/
    protected function getEmptyDefaultValue($field)
    {
        switch($field) {
            case 'phone':
                return '0000'; break;
            case 'firstname':
                return 'NA'; break;
            case 'lastname':
            default:
                return 'NA'; break;
        }

        return 'NA';
    }

    /**
     * Use EXO Debtor's street line 1 and 2 instead for the _address_street data
     * @param array $exoCustomerData
     * @param string $addressType (shipping|billing)
     * @return string
     */
    protected function getAddressStreet($exoCustomerData, $addressType)
    {
        $addressStreet = '';
        // deliveryaddress - shipping
        $addressKey = 'deliveryaddress';
        if ($addressType == 'billing') {
            // postaladdress
            $addressKey = 'postaladdress';
        }
        $line1 = $exoCustomerData[$addressKey]['line1'];
        $line2 = $exoCustomerData[$addressKey]['line2'];

        if (!empty($line1) && !empty($line2)) {
            $addressStreet = $line1 . "\n" . $line2;
        } else if (!empty($line1) && empty($line2)) {
            $addressStreet = $line1;
        } else if (empty($line1) && !empty($line2)) {
            $addressStreet = $line2;
        } else {
            $addressStreet = 'NA';
        }

        return $addressStreet;
    }

    /**
     * Get Billing / Shipping Address
     *
     * @param Array $exoCustomerData
     * @param String $addressType
     * @return Array $addressData
     **/
    protected function getAddressData($exoCustomerData, $addressType)
    {
    	$addressArray = array();
		$addressData = array();

    	switch($addressType) {
    		case 'billing':
    			$addressArray = $exoCustomerData['postaladdress'];
    			$postcode = $exoCustomerData['postalcode'];
    			break;

    		case 'shipping':
    			$addressArray = $exoCustomerData['deliveryaddress'];
    			$postcode = $exoCustomerData['deliveryaddress']['line6'];
    			break;
    	}

        $directPhoneNumber = '000';

        if(isset($exoCustomerData['defaultcontact']['directphonenumber'])){
            $directPhoneNumber = $exoCustomerData['defaultcontact']['directphonenumber'];
        }

    	if(!empty($addressArray)) {

    		$addressData = array(
    				'street'		=> $addressArray['line1'],
    				'city'			=> $addressArray['line4'],
    				'postcode'		=> $postcode,
    				'region'		=> $addressArray['line5'],
    				'telephone'		=> ($exoCustomerData['phone']) ? $exoCustomerData['phone'] : $directPhoneNumber,
    			);
    	}


    	return $addressData;
    }

    /**
     * Handler for multiple emails
     * @param string $email
     * @return string
     */
    public function getExoEmail($email)
    {
    	$email = strtolower($email);
    	$emailArray = explode(';', $email);
    	return (!empty($emailArray)) ? $emailArray[0] : 'NA';
    }

	/**
     * @return \Magento\ImportExport\Model\Import
     */
    public function createImportModel(){
        $importModel = $this->importModelFactory->create();
        $importModel->setData($this->settings);
        return $importModel;
    }

	public function processImport($dataArray)
	{
		if ($this->_validateData($dataArray)) {
			$this->_importData();

			$this->logger->debug('SETTING NEW CUSTOMER RANGE RESTRICTIONS');
			$this->setNewCustomerRangeRestrictions();
            $this->logger->debug('IMPORTED '.count($dataArray).' CUSTOMERS!');
			$this->logger->debug('DONE!');
		}
	}

    protected function _validateData($dataArray)
    {

        $importModel = $this->createImportModel();
        $source = $this->arrayAdapterFactory->create(array('data' => $dataArray));

        //$importModel->_getEntityAdapter()->setProcessedRowsCount(1);
        $this->validationResult = $importModel->validateSource($source);
        $this->addToLogTrace($importModel);

        return $this->validationResult;
    }

    protected function _importData()
    {
        $importModel = $this->createImportModel();
        $importModel->importSource();
        $this->_handleImportResult($importModel);
    }

    protected function _handleImportResult($importModel)
    {
        $errorAggregator = $importModel->getErrorAggregator();
        $this->errorMessages = $this->errorHelper->getImportErrorMessages($errorAggregator);
        $this->addToLogTrace($importModel);
        if (!$importModel->getErrorAggregator()->hasToBeTerminated()) {
            $importModel->invalidateIndex();
        }
    }

    /**
     * @param string $entityCode
     */
    public function setEntityCode($entityCode)
    {
        $this->settings['entity'] = $entityCode;

    }

    /**
     * @param string $behavior
     */
    public function setBehavior($behavior)
    {
        $this->settings['behavior'] = $behavior;
    }

    /**
     * @param string $strategy
     */
    public function setValidationStrategy($strategy)
    {
        $this->settings['validation_strategy'] = $strategy;
    }

    /**
     * @param int $count
     */
    public function setAllowedErrorCount($count)
    {
        $this->settings['allowed_error_count'] = $count;
    }

    /**
     * @param string $dir
     */
    public function setImportImagesFileDir($dir)
    {
        $this->settings['import_images_file_dir'] = $dir;
    }

    public function getValidationResult()
    {
        return $this->validationResult;
    }

    public function addToLogTrace($importModel){
        $this->logTrace = $this->logTrace.$importModel->getFormatedLogTrace();
    }

    public function getLogTrace()
    {
        return $this->logTrace;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function purgeDuplicateAddresses() {

	if (!empty($this->exoCustomersAddressToDelete)) {
	    $customer_ids = implode(',', $this->exoCustomersAddressToDelete);    
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
 	    $connection = $resource->getConnection();

            $sql = sprintf("
                  DELETE FROM customer_address_entity
                  WHERE parent_id IN ( SELECT entity_id FROM customer_entity WHERE website_id = %d)
                     AND entity_id NOT IN (
                      SELECT address_id FROM (
                        SELECT default_billing as address_id  FROM customer_entity WHERE default_billing IS NOT NULL
                        UNION
                        SELECT default_shipping as address_id FROM customer_entity WHERE default_shipping IS NOT NULL
                      ) default_addresses
		  )", $this->websiteUniqueId) ;

	    $connection->query($sql);
        }
    }

	/**
	 * Finish Import
	 */
	protected function finishImport()
	{
		$this->logger->debug('Reindexing');

		/**
		* SPL-335 - Don't reindex anymore, $indexer->reindexAll() lines.. indexer = customer_grid
		*/

		//record end time
		$this->endTime = microtime(true);
		$executionTime = ($this->endTime - $this->startTime ) / 60;
		$this->logger->debug('Finish Customer Import. Execution Time: ' . $executionTime);


		return $this;
	}

    /**
     * Set new customer range restrictions
     * @return void
     */
	public function setNewCustomerRangeRestrictions() {
		try {
			foreach ($this->rangeRestrictionSet as $newCustomer) {
				$loadedCustomer = $this->customerModel->setWebsiteId($this->websiteUniqueId)->loadByEmail($newCustomer['email']);

				if ($loadedCustomer) {
					$loadedCustomer->setRangeRestrictions($newCustomer['restriction'])->save();
				} else {
					$this->logger->debug("setNewCustomerRangeRestrictions :: Customer cannot be loaded");
					$this->logger->debug(print_r($newCustomer, true));
				}
			}
		} catch (\Exception $e) {
			$this->logger->debug($newCustomer['email'] . ' causing unknown Error');
			$this->logger->debug($e->getMessage());
		}
	}

    /**
     * SPL - get country ID depending on "current website id"
     * @return string $country
     */
    private function getCountryForCustomer()
    {
        $country = 'AU';
        $exoCurrentWebsiteId = $this->configHelper->getExoCurrentWebsiteId();
        if (!empty($exoCurrentWebsiteId) && (preg_match('/nz/', $exoCurrentWebsiteId))) {
            $country = 'NZ';
        }

        return $country;
    }
}
