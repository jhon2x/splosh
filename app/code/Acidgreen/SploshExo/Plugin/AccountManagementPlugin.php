<?php

namespace Acidgreen\SploshExo\Plugin;

use Acidgreen\Exo\Helper\Order as OrderHelper;
use Acidgreen\Exo\Helper\CustomerInterface as CustomerHelper;
use Acidgreen\Exo\Helper\Customer\DebtorContact as DebtorContactHelper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\Exo\Helper\Records\Tosync as ResyncHelper;

class AccountManagementPlugin
{
    /**
       * @var \Acidgreen\Exo\Helper\Order
       */
  	protected $orderHelper;

  	/**
  	 *
  	 * @var \Acidgreen\Exo\Helper\Customer
  	 */
  	protected $customerHelper;

  	/**
       * @var \Psr\Log\LoggerInterface
       */
  	protected $logger;

  	protected $customerFactory;

  	protected $customerResourceFactory;

  	/**
  	 *
  	 * @var \Acidgreen\Exo\Helper\Customer\DebtorContact
  	 */
  	protected $debtorContactHelper;

  	/**
  	 * @var StoreManagerInterface
  	 */
  	protected $_storeManager;

    /**
     * @var ResyncHelper
     */
    protected $resyncHelper;
    // $this->resyncHelper->queueForSync(5629, customer_create)

    /**
       * __construct
       *
       * @param OrderHelper $orderHelper
       * @param Logger $logger
       */
  	public function __construct (
    		OrderHelper $orderHelper,
    		CustomerHelper $customerHelper,
    		CustomerFactory $customerFactory,
    		CustomerResourceFactory $customerResourceFactory,
    		DebtorContactHelper $debtorContactHelper,
    		StoreManagerInterface $storeManager,
        ResyncHelper $resyncHelper,
    		Logger $logger
    )
  	{
    		$this->orderHelper = $orderHelper;
    		$this->customerHelper = $customerHelper;
    		$this->customerFactory = $customerFactory;
    		$this->customerResourceFactory = $customerResourceFactory;
    		$this->debtorContactHelper = $debtorContactHelper;
    		$this->logger = $logger;
    		$this->_storeManager = $storeManager;
        $this->resyncHelper = $resyncHelper;

  	}

    /**
     * Intercept \Magento\Customer\Model\AccountManagement::createAccount()
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function aroundCreateAccount(
        $subject,
        $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $newCustomer,
        $password = null,
        $redirectUrl = ''
    ) {
        $customerData = $proceed($newCustomer, $password, $redirectUrl);

        /**
         * SPL-466 - disable pushing of customer data towards EXO
         */
        $this->logger->debug('SPL-466 :: STOP pushing towards EXO...');
        return $customerData;

        // If not enabled, return
        if (!$this->customerHelper->isExoDebtorCreationEnabled()) return $customerData;

        try {
            $this->logger->debug('ATTENTION::' . __METHOD__ . ' -- RUN FROM frontend');
            if (is_object($customerData)) {

            $currentWebsiteCode = $this->_storeManager->getWebsite()->getCode();

            $response = $this->debtorContactHelper->createExoCustomerDebtorContact($customerData);

            if (!empty($response['error_response'])) {
                $this->logger->debug(__('%1 :: error creating Debtor Contact!', __METHOD__));
                $this->logger->debug(print_r($response['error_response'], true));
                $this->logger->debug(__('%1 :: Queue for sync later via $this->resyncHelper->queueForSync(%2, %3)', __METHOD__, $customerData->getId(), 'customer_create'));
                $this->resyncHelper->queueForSync($customerData->getId(), $currentWebsiteCode, 'customer_create');
            }

            $this->logger->debug('ATTENTION :: ' . __METHOD__ . ' :: response below ');
            $this->logger->debug(print_r($response, true));

            if (!is_array($response)) {
                $responseArray = json_decode($response, true);

                if (json_last_error() == JSON_ERROR_NONE) {
                    $this->logger->debug(__('ATTENTION :: responseArray ...'));
                    $this->logger->debug(print_r($responseArray, true));
                    $customerData->setCustomAttribute('exo_customer_defaultcontactid', $responseArray['id']);
                    $customer = $this->customerFactory->create()->load($customerData->getId());

                    $this->logger->debug(__('%1 :: Set Group ID Line %2', __METHOD__, __LINE__));
                    $customer->setGroupId($this->customerHelper->getDebtorB2cMagentoGroupId($currentWebsiteCode))->save();
                    $this->logger->debug(__('%1 :: Group ID updated.', __METHOD__));
                    $customer->updateData($customerData);

                    $this->logger->debug('creating customerResource...');
                    $customerResource = $this->customerResourceFactory->create();
                    $customerResource->saveAttribute($customer, 'exo_customer_defaultcontactid');

                    $this->logger->debug('creating Debtor account');
                        // $response = $this->debtorContactHelper->createExoCustomerDebtorContact($customerData, $responseArray);
                        $response = $this->customerHelper->createExoCustomerDebtor($customerData, $responseArray);

                        if (!empty($response['error_status'])) {
                            $this->logger->debug(__('%1 :: Error creating EXO Debtor', __METHOD__));
                            $this->logger->debug(print_r($response['body'], true));
                            // Queue for update later here...
                            $this->logger->debug(__('%1 :: Queue for sync later via $this->resyncHelper->queueForSync(%2, %3)', __METHOD__, $customerData->getId(), 'customer_create'));
                            // resync?:
                            $this->resyncHelper->queueForSync($customerData->getId(), $currentWebsiteCode, 'customer_create');
                            // throw new \Exception('Cannot create EXO Debtor. See log for details.');
                        } else {
                            $this->logger->debug('Decoding response');
                            $responseArray = json_decode($response, true);
                            if (json_last_error() == JSON_ERROR_NONE) {
                                // save exo_customer_default_contact_id
                                $customerData->setCustomAttribute('exo_customer_id', $responseArray['id']);
                                $customer->updateData($customerData);
                                $customerResource->saveAttribute($customer, 'exo_customer_id');
                            }
                        }
                    }

                } else {
                    // Queue for resync
                    $this->resyncHelper->queueForSync(
                        $customerData->getId(), 
                        $currentWebsiteCode, 
                        'customer_create');
                }
            } else {
                $this->logger->debug('Data type of customerData : ' . print_r(gettype($customerData), true));
            }

            $this->customerHelper->triggerCustomerReindex();

            return $customerData;
        } catch (\Exception $e) {
        $exceptionMessage = $e->getMessage();
            $this->logger->debug(__('ERROR SYNCING TO EXO: %1', $exceptionMessage));
            $this->logger->critical(__('CRITICAL :: ERROR SYNCING TO EXO: %1', $exceptionMessage));
        }

        return $customerData;
    }
}
