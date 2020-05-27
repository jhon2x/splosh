<?php

namespace Acidgreen\SploshExo\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Acidgreen\Exo\Helper\Api\Api as ApiHelper;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Acidgreen\Exo\Helper\CustomerInterface as CustomerHelperInterface;
use Magento\Indexer\Model\IndexerFactory;
use Acidgreen\SploshExo\Model\SploshCustomerGroupFactory;
use Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\Collection as SploshCustomerGroupCollection;
use Magento\Customer\Model\ResourceModel\GroupRepository as MageGroupRepository;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper implements CustomerHelperInterface
{
    const CONFIG_CUSTOMER_REQUIRED_REGULAR_FIELDS = 'acidgreen_exo_customer/exo_required_fields/exo_required_regular_fields';

    const CONFIG_CUSTOMER_REQUIRED_EXTRA_FIELDS = 'acidgreen_exo_customer/exo_required_fields/exo_required_extra_fields';

    const CONFIG_DEBTOR_B2C_PRIMARYGROUPID = 'acidgreen_exo_customer/customer_settings/exo_debtor_b2c_primarygroupid';

    const CONFIG_DEBTOR_B2C_SECONDARYGROUPID = 'acidgreen_exo_customer/customer_settings/exo_debtor_b2c_secondarygroupid';

    const CONFIG_DEBTOR_B2C_MAGENTO_GROUP_ID = 'acidgreen_exo_customer/customer_settings/exo_debtor_b2c_magento_group_id';

    const CONFIG_DEBTOR_B2C_SALESPERSONID = 'acidgreen_exo_customer/customer_settings/debtor_b2c_salespersonid';

    const REQUIRED_DEBTOR_UPDATE_FIELDS = 'acidgreen_exo_customer/exo_required_fields/debtor_update_fields';

    const CONFIG_B2B_WEBSITE_CODES = 'acidgreen_exo_apisettings/sync_settings/b2b_website_codes';

    const REAL_PASSWORD_HASH_LENGTH = 99;
    /**
     *
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var Acidgreen\Exo\Helper\Api\Api
     */
    protected $apiHelper;

    /**
     *
     * @var Acidgreen\Exo\Helper\Api\Config
     */
    protected $configHelper;

    /**
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected $_storeManager;

    protected $emailAddressValidator;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customersWithoutAddressCollection;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * SPL-379 - Encryptor interface
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Splosh Customer Group collection
     *
     * @var \Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\Collection
     */
    protected $sploshCustomerGroupCollection;

    /**
     * Splosh Customer Group Factory
     *
     * @var SploshCustomerGroupFactory
     */
    protected $sploshCustomerGroupFactory;


    /**
     * Magento Customer Group Repository
     *
     * @var MageGroupRepository
     */
    protected $mageGroupRepository;

    /**
     * Class __construct
     *
     * @param ApiHelper $apiHelper
     * @param ConfigHelper $configHelper
     * @param CustomerFactory $customerFactory
     * @param Logger $logger
     * @param SploshCustomerGroupCollection $sploshCustomerGroupCollection
     * @param SploshCustomerGroupFactory $sploshCustomerGroupFactory
     * @param MageGroupRepository $mageGroupRepository
     */
    public function __construct(
        ApiHelper $apiHelper,
        ConfigHelper $configHelper,
        CustomerFactory $customerFactory,
        Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        EmailAddressValidator $emailAddressValidator,
        IndexerFactory $indexerFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        SploshCustomerGroupCollection $sploshCustomerGroupCollection,
        SploshCustomerGroupFactory $sploshCustomerGroupFactory,
        MageGroupRepository $mageGroupRepository
    ) {
        $this->apiHelper = $apiHelper;
        $this->configHelper = $configHelper;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->_storeManager = $_storeManager;

        $this->emailAddressValidator = $emailAddressValidator;
        $this->indexerFactory = $indexerFactory;
        $this->encryptor = $encryptor;
        $this->sploshCustomerGroupCollection = $sploshCustomerGroupCollection;
        $this->sploshCustomerGroupFactory = $sploshCustomerGroupFactory;
        $this->mageGroupRepository = $mageGroupRepository;
    }

    /**
     * Wrapper function to create Customer details to EXO as a new Debtor
     * @param array $customerData
     * @param array $debtorContactResponseArray
     * @return array|null|string
     */
    public function createExoCustomerDebtor($customerData, array $debtorContactResponseArray)
    {
        $debtorResponse = '';
        $exoCustomerDebtorRequest = $this->formatCustomerDebtorRequest($customerData);
        $exoCustomerDebtorRequest['defaultcontactid'] = $debtorContactResponseArray['id'];

        $debtorResponse = $this->apiHelper->sendCustomer($exoCustomerDebtorRequest);

        if ($debtorResponse['status'] == '201') { // 201 - Resource was created
            $body = \GuzzleHttp\Ring\Core::body($debtorResponse);

            // response should be in XML since we've set accept application/xml

            return $body;
        } else {
            $tempDebtorResponse = $debtorResponse;
            $tempDebtorResponse['body'] = \GuzzleHttp\Ring\Core::body($debtorResponse);
            $tempDebtorResponse['error_status'] = $debtorResponse['status'];
            $debtorResponse = $tempDebtorResponse;
        }

        return $debtorResponse;
    }

    /**
     * Format data to use for POST request (creation of Debtor at EXO)
     *
     * @todo Configurable required fields
     * @param  $customerData
     * @return string[]|NULL[]
     */
    // public muna
    public function formatCustomerDebtorRequest($customerData)
    {
        $exoCustomerDebtorRequest = [];

        if (!empty($customerData)) {
            $customerModel = $customerData;

            // format required fields
            $exoCustomerDebtorRequest['accountname'] = $customerModel->getFirstname() . ' ' . $customerModel->getLastname();

            $requiredRegularFields = $this->getRequiredRegularFields();
            foreach ($requiredRegularFields as $requiredField => $fieldDetails) {
                $exoCustomerDebtorRequest[$requiredField] = $this->mapExoValue($customerModel, $fieldDetails);
            }


            $exoCustomerDebtorRequest['extrafields'] = [];
            // parse required extra fields and their values?
            $requiredExtraFields = $this->getRequiredExtraFields();
            foreach ($requiredExtraFields as $requiredField => $fieldDetails) {
                array_push($exoCustomerDebtorRequest['extrafields'], [
                    'key' => $requiredField,
                    'value' => $this->mapExoValue($customerModel, $fieldDetails),
                ]);
            }

            // SPL-50 - Set EXO primarygroupid
            $exoCustomerDebtorRequest['primarygroupid'] = $this->configHelper->getScopeConfigWebsite(
                self::CONFIG_DEBTOR_B2C_PRIMARYGROUPID,
                $this->configHelper->getExoCurrentWebsiteId()
            );

            // Salespersonid
            $exoCustomerDebtorRequest['salespersonid'] = $this->configHelper->getScopeConfigWebsite(
                // josephson
                self::CONFIG_DEBTOR_B2C_SALESPERSONID,
                $this->configHelper->getExoCurrentWebsiteId()
            );

            // SPL-122 - for sync purposes - website
            $exoCustomerDebtorRequest['website'] = $this->_storeManager->getStore()->getBaseUrl();

        }

        return $exoCustomerDebtorRequest;

    }

    /**
     * Check if creation of EXO debtor via magento customer registration is enabled
     *
     * @return boolean
     */
    public function isExoDebtorCreationEnabled()
    {
        return true;
    }

    public function getDebtorB2cPrimaryGroupId($website = 'base')
    {
        return $this->configHelper->getScopeConfigWebsite(
            self::CONFIG_DEBTOR_B2C_PRIMARYGROUPID,
            $website
        );
    }

    public function getDebtorB2cMagentoGroupId($website = 'base', $exoCustomerData)
    {
        if($this->isCurrentWebsiteB2B()){
            $newExoGroup = true;
            $mage_group_id = $this->configHelper->getScopeConfigWebsite(
                self::CONFIG_DEBTOR_B2C_MAGENTO_GROUP_ID,
                $website
            );
            try{
                foreach($this->sploshCustomerGroupCollection->load() as $mapping){
                    if($mapping->getData()['exo_group_id'] == $exoCustomerData['primarygroupid']){
                        $mage_group_id = $mapping->getData()['mage_group_id'];
                        $newExoGroup = false;
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->debug('Customer Group Mapping Failed, invalid data : ');
                $this->logger->debug(print_r($exoCustomerData, true));
                $this->logger->debug($e->getMessage());
            }

            if($newExoGroup){
                try {
                    $mageId = (int)$mage_group_id;
                    $exoId = (int)$exoCustomerData['primarygroupid'];
                    $description = $exoCustomerData['primarygroup']['name'];

                    /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
                    $customerGroup = $this->sploshCustomerGroupFactory->create();
                    $customerGroup->setMageGroupId($mageId);
                    $customerGroup->setExoGroupId($exoId);
                    $customerGroup->setDescription($description);
                    $customerGroup->save($customerGroup);

                    $this->sploshCustomerGroupCollection->clear();

                    $this->logger->debug("Successfully saved new customer group mapping mage_group_id: $mageId exo_group_id: $exoId");
                } catch (\Exception $e) {
                    $this->logger->debug("Failed to saved new customer group mapping mage_group_id: $mageId exo_group_id: $exoId");
                    $this->logger->debug(print_r($e->getMessage(),true));
                }
            }

            return $mage_group_id;
        }else{
            return $this->configHelper->getScopeConfigWebsite(
                self::CONFIG_DEBTOR_B2C_MAGENTO_GROUP_ID,
                $website
            );
        }
    }

    /**
     * Get required "regular" fields (accountname? email? credittermid? etc...)
     * @return array
     */
    protected function getRequiredRegularFields()
    {
        return $this->getRequiredFields(self::CONFIG_CUSTOMER_REQUIRED_REGULAR_FIELDS);
    }

    /**
     * Get required EXTRA FIELDS
     *
     * @return array
     */
    protected function getRequiredExtraFields()
    {
        return $this->getRequiredFields(self::CONFIG_CUSTOMER_REQUIRED_EXTRA_FIELDS);
    }

    /**
     * Get required fields
     * (regular required fields or custom extra fields set by client)
     * Usage:
     * $this->getRequiredFields(self::CONFIG_CUSTOMER_REQUIRED_REGULAR_FIELDS); (regular)
     * $this->getRequiredFields(self::CONFIG_CUSTOMER_REQUIRED_EXTRA_FIELDS); (extra fields)
     *
     * @return array
     */
    protected function getRequiredFields($scopeConfigPath)
    {
        $fields = [];

        $requiredFields = $this->configHelper->getScopeConfigWebsite(
            $scopeConfigPath,
            $this->configHelper->getExoCurrentWebsiteId()
        );

        // Don't parse if there's no config for it,
        // return the empty array instead
        if (!empty($requiredFields))
            $fields = $this->parseRequiredFields($requiredFields);

        return $fields;
    }


    private function parseRequiredFields($requiredFieldsString)
    {
        $fields = [];

        $line = strtok($requiredFieldsString, \Acidgreen\Exo\Helper\Api\Config::STRTOK_SEPARATOR);

        // data is in CSV
        // parse it
        // then return
        while ($line !== FALSE) {
            $lineArray = explode(',', $line);

            if (count($lineArray) == 3) {
                $fields[$lineArray[0]] = [
                    'exo_field' => $lineArray[0],
                    'attribute' => $lineArray[1],
                    'default' => $lineArray[2]
                ];
            }

            $line = strtok(\Acidgreen\Exo\Helper\Api\Config::STRTOK_SEPARATOR);
        }
        return $fields;
    }

    /**
     * Map corresponding value for EXO sync
     *
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @param array $fieldDetails
     * @return string
     */
    private function mapExoValue(
        \Magento\Customer\Model\Data\Customer $customer,
        array $fieldDetails
    )
    {
        // field 1 - field in EXO
        // field 2 - attribute to be used in getter
        // (should be in proper casing already
        // - e.g. Email for getEmail, FirstNameWhatever for getFirstnameWhatever)
        // field 3 - default value if empty
        if (!empty($fieldDetails['attribute'])) {
            $getter = 'get'.$fieldDetails['attribute'];
            $value = $customer->$getter();
        } else {
            $value = $fieldDetails['default'];
        }

        // return default value if any?
        return (!empty($value)) ? $value : '';
    }

    //----------D E B T O R   U P D A T E----------//

    /**
     * Update EXO Debtor account
     * @param array $customerData
     * @return string
     */
    public function updateExoCustomerDebtor(array $customerData)
    {
        $debtorResponse = $this->apiHelper->sendCustomer($customerData, 'PUT');

        if (!empty($debtorResponse['status']) && $debtorResponse['status'] == '200') {
            $body = \GuzzleHttp\Ring\Core::body($debtorResponse);
            return $body;
        }

        return [
            'status' => (!empty($debtorResponse['status'])) ? $debtorResponse['status'] : '',
            'error_code' => -1,
            'message' => 'UPDATE FAILED.',
            'body' => \GuzzleHttp\Ring\Core::body($debtorResponse)
        ];
    }

    /**
     * Process Customer details for EXO debtor update
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Address $customerAddress
     * @todo CORRECT primarygroupid update value between b2c and b2b, it should be accdg to the config
     * @return array
     */
     public function processCustomerUpdate(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Address $customerAddress
    ) {
        $requiredFieldsData = [];
        // get required fields
        $requiredFields = $this->getRequiredDebtorUpdateFields();
        
        // loop through required fields and map data
        foreach ($requiredFields as $exoField => $exoFieldMapping) {
          // process EXO data here...
          $requiredFieldsData[$exoField] = $this->mapExoCustomerData($customer, $exoFieldMapping);
        }

        // default values
        
        $requiredFieldsData['accountname'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        $requiredFieldsData['postalcode'] = '1234';
        /**
        * SPL-71 - Disable this
        */
        // Company name
        // if (!empty($customerAddress->getCompany())) {
        //	$requiredFieldsData['accountname'] = $customerAddress->getCompany();
          // }
        // Telephone
        if (!empty($customerAddress->getTelephone()))
        $requiredFieldsData['phone'] = $customerAddress->getTelephone();
        // Postalcode
        if (!empty($customerAddress->getPostcode()))
        $requiredFieldsData['postalcode'] = $customerAddress->getPostcode();
        // Email
        $requiredFieldsData['email'] = $customer->getEmail();
        
        $requiredFieldsData['website'] = $this->_storeManager->getStore()->getBaseUrl();
        
        /**
        * SPL-171 - Value assigned SHOULD be based on PER WEBSITE configuration
        */
        if ($this->configHelper->hasExoCurrentWebsiteId()) {
            $requiredFieldsData['primarygroupid'] = $this->configHelper->getScopeConfigWebsite(
                  self::CONFIG_DEBTOR_B2C_PRIMARYGROUPID,
                  $this->configHelper->getExoCurrentWebsiteId());
            
            $requiredFieldsData['secondarygroupid'] = $this->configHelper->getScopeConfigWebsite(
                  self::CONFIG_DEBTOR_B2C_SECONDARYGROUPID,
                  $this->configHelper->getExoCurrentWebsiteId());
            
            $requiredFieldsData['salespersonid'] = $this->configHelper->getScopeConfigWebsite(
                  self::CONFIG_DEBTOR_B2C_SALESPERSONID,
                  $this->configHelper->getExoCurrentWebsiteId());
        } else {
            $requiredFieldsData['primarygroupid'] = $this->configHelper->getScopeConfigWebsite(self::CONFIG_DEBTOR_B2C_PRIMARYGROUPID);
          
            $requiredFieldsData['secondarygroupid'] = $this->configHelper->getScopeConfigWebsite(self::CONFIG_DEBTOR_B2C_SECONDARYGROUPID);
          
            $requiredFieldsData['salespersonid'] = $this->configHelper->getScopeConfigWebsite(self::CONFIG_DEBTOR_B2C_SALESPERSONID);
        }

            $isCurrentWebsiteB2B = $this->isCurrentWebsiteB2B();

            $requiredFieldsData['extrafields'] = [
                [
                    'key' => 'X_USERNAME',
                    'value' => $customer->getEmail()
                ]
            ];

        /**
        * @todo process accountname mapping distinction here...
        */
        // if b2b
        $this->logger->debug(__METHOD__.' :: isCurrentWebsiteB2B :: '.print_r($isCurrentWebsiteB2B, true));
        if ($isCurrentWebsiteB2B) {
            // map company here...
            if (!empty($customerAddress->getCompany())) {
                $requiredFieldsData['accountname'] = $customerAddress->getCompany();
            }

         // $requiredFieldsData['salespersonid'] = $customer->getExoDebtorSalespersonid();
         $requiredFieldsData['salespersonid'] = $customer->getSalesperson();
        }

        return $requiredFieldsData;
    }

    /**
     * Get required fields when updating a Debtor (a Magento Customer)
     *
     * @return array
     */
    private function getRequiredDebtorUpdateFields()
    {
        $exoCurrentWebsiteId = 'base';
        if ($this->configHelper->hasExoCurrentWebsiteId())
            $exoCurrentWebsiteId = $this->configHelper->getExoCurrentWebsiteId();

        $fields = $this->configHelper->getScopeConfigWebsite(self::REQUIRED_DEBTOR_UPDATE_FIELDS, $exoCurrentWebsiteId);

        return $this->configHelper->parseCsvFieldsConfig($fields);
    }

    /**
     * Map Magento customer data to corresponding EXO Debtor field
     *
     * @todo what if the getter return value is an array/object/whatever-complicated-data
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $exoFieldMapping
     * @return mixed $value
     */
    private function mapExoCustomerData(
        \Magento\Customer\Model\Customer $customer,
        array $exoFieldMapping
    ) {
        $this->logger->debug(__METHOD__);
        $value = '';

        if (!empty($exoFieldMapping['attribute'])) {
            $getter = 'get'.ucfirst($exoFieldMapping['attribute']);

            $value = $customer->$getter();
        }

        if (empty($value) && !empty($exoFieldMapping['default'])) {

            $value = $exoFieldMapping['default'];

            // handle boolean values...
            if ($value == "true")
                $value = true;
            else if ($value == "false")
                $value = false;
        }

        // handle zeros...
        if ("0" == $exoFieldMapping['default']) {
            $value = $exoFieldMapping['default'];
        }

        if (is_null($value))
            $value = '';


        return $value;
    }

    /**
     * Validate email
     * @param string $email
     * @return boolean
     */
    public function isEmailValid($email) {
        return $this->emailAddressValidator->isValid($email);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Acidgreen\Exo\Helper\CustomerInterface::mapExclusiveCustomerColumns()
     */
         public function mapExclusiveCustomerColumns($exoCustomerData)
        {
            $customerData = [];
             // take note that data is in JSON format
             // X_USERNAME => email
             $customerData['email'] = $exoCustomerData['extrafields'][16]['value'];

            return $customerData;
        }

    /**
     *
     * {@inheritDoc}
     * @see \Acidgreen\Exo\Helper\CustomerInterface::getPassword()
     */
    public function getPassword($exoCustomerData)
    {
        return $this->encryptor->getHash($this->_getPassword($exoCustomerData), true);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Acidgreen\Exo\Helper\CustomerInterface::getExoEmail()
     */
    public function getExoEmail($exoCustomerData)
    {
        $email = $exoCustomerData['extrafields'][16]['value'];
        $email = strtolower($email);
        $emailArray = explode(';', $email);
        return (!empty($emailArray)) ? $emailArray[0] : 'NA';
    }

    /**
     *
     * {@inheritDoc}
     * @see \Acidgreen\Exo\Helper\CustomerInterface::isValidForImport()
     */
    public function isValidForImport($exoCustomerData)
    {
        // consider the exo website code instead?
        $currentWebsite = $this->configHelper->getExoCurrentWebsiteId();

        if($this->isCurrentWebsiteB2B() && $exoCustomerData['salesperson']['name'] == 'MEMBER OF THE PUBLIC'){
            $this->logger->info('isValidForImport ATTENTION :: CUSTOMER IMPORT :: '.$exoCustomerData['id'].' NOT VALID FOR IMPORT AS customer salesperson name is MEMBER OF THE PUBLIC. Also EXO current website id :: '.$currentWebsite);
            return false;
        } elseif(!$this->isCurrentWebsiteB2B() && $exoCustomerData['salesperson']['name'] != 'MEMBER OF THE PUBLIC') {
            $this->logger->info('isValidForImport ATTENTION :: CUSTOMER IMPORT :: '.$exoCustomerData['id'].' NOT VALID FOR IMPORT AS customer salesperson name is not MEMBER OF THE PUBLIC. Also EXO current website id :: '.$currentWebsite);
            return false;
        }

        // check email validity (since we use X_USERNAME for it already!)
        $email = $exoCustomerData['extrafields'][16]['value'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logger->info('isValidForImport ATTENTION :: CUSTOMER IMPORT :: '.$exoCustomerData['id'].' NOT VALID FOR IMPORT AS X_USERNAME/website user contains invalid email.$email = '.print_r([$email], true).' Also EXO current website id :: '.$currentWebsite);
            return false;
        }

        /**
         * SPL-355 - Customers registered from Magento/B2C w/o addresses yet shouldn't be synced from EXO
         * Pulling them from EXO produces N/A on their addresses
         */
        if ($this->customerHasNoAddressAndFromB2C($exoCustomerData)) {
            $this->logger->info('isValidForImport ATTENTION :: CUSTOMER IMPORT :: '.$exoCustomerData['id']. ' NOT VALID FOR IMPORT AS IT IS A B2C CUSTOMER W/O ADDRESS YET...');
            return false;
        }
        
        return true;
    }

    /**
     * Reindex customer grid
     * @return void
     */
    public function triggerCustomerReindex()
    {
        /**
         * SPL-352 - Don't trigger $customerGridIndexer->reindexAll() anymore
         * it may collide with EXO sync being ran the same time a Customer registers/does some action
         */
        // $customerGridIndexer = $this->indexerFactory->create();
        // $customerGridIndexer->load('customer_grid');
        // $customerGridIndexer->reindexAll();
    }

    public function isCurrentWebsiteB2B()
    {
        $currentWebsiteCode = $this->_storeManager->getWebsite()->getCode();

        if ($this->configHelper->hasExoCurrentWebsiteId()) {
            $currentWebsiteCode = $this->configHelper->getExoCurrentWebsiteId();
        }
        $this->logger->debug(__METHOD__.' :: currentWebsiteCode changed to :: '.$currentWebsiteCode);

        $b2bWebsiteCodes = $this->configHelper->getScopeConfigWebsite(self::CONFIG_B2B_WEBSITE_CODES);

        return preg_match("/".$currentWebsiteCode."/", $b2bWebsiteCodes);
    }

    /**
     * SPL-355 - Customers registered from Magento/B2C w/o addresses yet shouldn't be synced from EXO
     * Pulling them from EXO produces N/A on their addresses
     */
    protected function customerHasNoAddressAndFromB2C($exoCustomerData) {
        $collection = $this->getCustomersWithoutAddressCollection();
        return (isset($collection[$exoCustomerData['id']]));
    }

    protected function getCustomersWithoutAddressCollection()
    {
        if (!$this->customersWithoutAddressCollection) {
            $collection = $this->customerFactory->create()->getCollection();
            $collection->addAttributeToSelect(['exo_customer_id'], 'inner');
            $collection->addFieldToFilter('default_shipping', ['null' => true]);
            $collection->addFieldToFilter('default_billing', ['null' => true]);
            // add website_id filter too..
            $websiteId = $this->configHelper->getExoCurrentWebsiteId();
            /**
             * SPL-355 - For B2C only!
             */
            if (!is_numeric($websiteId) && !$this->isCurrentWebsiteB2B()) {
                $b2cWebsiteIds = \Acidgreen\SploshExo\Helper\Api\Config::B2C_WEBSITE_CODES_IDS;
                $websiteId = $b2cWebsiteIds[$websiteId];
            }
            $collection->addFieldToFilter('website_id', $websiteId);

            $this->logger->info('SPL-355 :: customersWithoutAddressCollection SQL :: '.$collection->getSelect()->__toString());

            $this->customersWithoutAddressCollection = [];
            if (count($collection) > 0) {
                foreach ($collection as $customer) {
                    // $customer object might not be needed?
                    $this->customersWithoutAddressCollection[$customer->getExoCustomerId()] = $customer;
                }
            }
        }

        return $this->customersWithoutAddressCollection;
    }

    /**
     * Validate hash
     * @return boolean
     */
    public function getPasswordForExistingUser($exoCustomerData, $existingCustomer)
    {
        $password = $this->_getPassword($exoCustomerData);
        // only for B2B as B2C users might've changed password already (really)
        if ($this->isCurrentWebsiteB2B()) {
            // means that the stored password_hash was wrong
            if (!$this->encryptor->validateHash($password, $existingCustomer->getPasswordHash())) {
                // check length of hash is < 99. If so, get the one from EXO instead
                if (strlen($existingCustomer->getPasswordHash()) < self::REAL_PASSWORD_HASH_LENGTH)
                    return $this->encryptor->getHash($password, true);
            }
        }

        return $existingCustomer->getPasswordHash();
    }

    private function _getPassword($exoCustomerData)
    {
        $password = 'Password123';
        /**
         * SPL-438
         * Since we will only have brand new B2B customers now we will always be defaulting to 'Password123' when accounts are initially created
         */

        /*
        $currentWebsiteId = $this->_storeManager->getWebsite($this->configHelper->getExoCurrentWebsiteId())->getId();

        $b2bWebsiteIds = $this->configHelper->getScopeConfigWebsite(
        \Acidgreen\SploshBox\Model\Config\Source\B2BWebsite::CONFIG_B2B_WEBSITE_ID_PATH
        );

        // 18th element
        $exoCustomerDataPassword = $exoCustomerData['extrafields'][17]['value'];

        if (!empty($exoCustomerDataPassword)) 
        {
            $password = $exoCustomerDataPassword;
        }
         */

        return $password;
    }

    public function getCustomerMageGroupById($groupId){
        return $this->mageGroupRepository->getById($groupId);
    }
}
