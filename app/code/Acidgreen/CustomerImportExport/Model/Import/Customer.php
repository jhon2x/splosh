<?php

namespace Acidgreen\CustomerImportExport\Model\Import;


use Magento\CustomerImportExport\Model\Import\Customer as CoreImportExportCustomer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;


class Customer extends CoreImportExportCustomer
{
	protected $logger;
	
	
	public function __construct(
		\Magento\Framework\Stdlib\StringUtils $string,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\ImportExport\Model\ImportFactory $importFactory,
		\Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		ProcessingErrorAggregatorInterface $errorAggregator,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\ImportExport\Model\Export\Factory $collectionFactory,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory,
		\Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Psr\Log\LoggerInterface $logger,
		array $data = []
	) {
		$this->logger = $logger;
		parent::__construct(
			$string,
			$scopeConfig,
			$importFactory,
			$resourceHelper,
			$resource,
			$errorAggregator,
			$storeManager,
			$collectionFactory,
			$eavConfig,
			$storageFactory,
			$attrCollectionFactory,
			$customerFactory,
			$data
		);
	}
     /**
      * Validate row data for add/update behaviour
      *
      * @param array $rowData
      * @param int $rowNumber
      * @return void
      * @SuppressWarnings(PHPMD.CyclomaticComplexity)
      * @SuppressWarnings(PHPMD.NPathComplexity)
      */
     protected function _validateRowForUpdate(array $rowData, $rowNumber)
     {
     	
     	$minPasswordLength = $this->_scopeConfig->getValue(\Magento\Customer\Model\AccountManagement::MIN_PASSWORD_LENGTH);
     	
         if ($this->_checkUniqueKey($rowData, $rowNumber)) {
             $email = strtolower($rowData[self::COLUMN_EMAIL]);
             $website = $rowData[self::COLUMN_WEBSITE];

             if (isset($this->_newCustomers[strtolower($rowData[self::COLUMN_EMAIL])][$website])) {
                 $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNumber);
             }
             $this->_newCustomers[$email][$website] = false;

             if (!empty($rowData[self::COLUMN_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                 $this->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
             }
             // check password
             if (isset(
                 $rowData['password']
             ) && strlen(
                 $rowData['password']
             ) && $this->string->strlen(
                 $rowData['password']
             ) < $minPasswordLength
             ) {
                 $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNumber);
             }
             // check simple attributes
             foreach ($this->_attributes as $attributeCode => $attributeParams) {
                 if (in_array($attributeCode, $this->_ignoredAttributes)) {
                     continue;
                 }
                 if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                     $this->isAttributeValid($attributeCode, $attributeParams, $rowData, $rowNumber, ';');
                 } elseif ($attributeParams['is_required'] && !$this->_getCustomerId($email, $website)) {
                     $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                 }
             }
         }
    }
}
