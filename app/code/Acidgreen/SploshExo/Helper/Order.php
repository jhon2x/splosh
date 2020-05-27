<?php

namespace Acidgreen\SploshExo\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Acidgreen\SploshExo\Helper\Api as ApiHelper;
use Acidgreen\SploshExo\Helper\Api\Config as ConfigHelper;
use Acidgreen\SploshExo\Helper\Customer as CustomerHelper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;

class Order extends \Acidgreen\Exo\Helper\Order
{
    CONST CONFIG_SETTINGS_GUESTID   = 'acidgreen_exo_apisettings/settings/guestid';

    CONST CONFIG_SETTINGS_INTERNALID   = 'acidgreen_exo_apisettings/settings/internalid';

    CONST CONFIG_SETTINGS_INTERNALGROUP   = 'acidgreen_exo_apisettings/settings/internalgroup';

    CONST CONFIG_SETTINGS_PRICE_INCL_TAX   = 'tax/calculation/price_includes_tax';

    /** Member of the Public ID **/
    CONST SALES_MOTP_ID = '5321';

    /** Ready to Pick status "0. None" */
    const X_NAV_READY_STATUS_NONE = 0;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Acidgreen\Exo\Helper\Api\Api
     */
    protected $apiHelper;

    /**
     * @var \Acidgreen\Exo\Helper\Api\Config
     */
    protected $configHelper;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var \Acidgreen\Checkout\Helper\Cart
     */
    protected $agCheckoutHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     *
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
     protected $websiteCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * __construct
     *
     * @param ApiHelper $apiHelper
     * @param ConfigHelper $configHelper
     * @param CustomerHelper $customerHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerFactory $customerModel
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param Logger $logger
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculation
     */
    public function __construct(
        ApiHelper $apiHelper,
        ConfigHelper $configHelper,
        CustomerHelper $customerHelper,
        ScopeConfigInterface $scopeConfig,
        CustomerFactory $customerModel,
        StoreCollectionFactory $storeCollectionFactory,
        Logger $logger,
        WebsiteCollectionFactory $websiteCollectionFactory,
        \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation
    ){

        $this->apiHelper            = $apiHelper;
        $this->configHelper         = $configHelper;
        $this->customerHelper       = $customerHelper;
        $this->scopeConfig          = $scopeConfig;
        $this->customerModel        = $customerModel;
        $this->logger               = $logger;
        $this->agCheckoutHelper     = $agCheckoutHelper;

        $this->storeCollectionFactory = $storeCollectionFactory;

        $this->websiteCollectionFactory     = $websiteCollectionFactory;

        // SPL-313
        $this->storeManager = $storeManager;

        // SPL-411
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * Create EXO Sales Order
     *
     * @param Array $orderData
     * @return String | Bool
     */
    public function sendExoSalesOrder($orderData)
    {
        $exoOrderRequest = $this->formatOrderRequest($orderData);

        if($exoOrderRequest) {
            //send validate to api
            $validateOrderResponse = $this->apiHelper->sendOrderValidate($exoOrderRequest);

            if($validateOrderResponse['status'] == '200') {

                //if the validated order is successful

                $body   = \GuzzleHttp\Ring\Core::body($validateOrderResponse);

                $validatedOrderRequest = json_decode($body,true);

                if($validatedOrderRequest['order']) {
                    //send order data to Exo
                    $orderResponse = $this->apiHelper->sendSalesOrder($validatedOrderRequest['order']);

                    $responseBody   = \GuzzleHttp\Ring\Core::body($orderResponse);

                    $exoOrderData = json_decode($responseBody,true);

                    return (!empty($exoOrderData['id'])) ? $exoOrderData['id'] : false;

                }
            }

            //$this->logger->debug(print_r($data['customers'],true));

        }

        return false;
    }

    /**
     * Format Sales Order Request
     *
     * @param Array $orderData
     * @return Array
     */
    protected function formatOrderRequest($orderData)
    {

        $exoOrderRequest = array();

        if($orderData) {
            $currentWebsite = $this->storeManager->getWebsite();

            $comment = NULL;
            $request_body = file_get_contents('php://input');
            $this->logger->debug('DEBUG COMMENT: '.$request_body);
            $data = json_decode($request_body, true);

            if (isset ($data['comments'])) {
                if ($data['comments']) {
                    $comment = strip_tags ($data['comments']);
                }
            }

            /**
             * Use specific scope - NZ/AU
             */
            $this->logger->debug('SPL-343 or SPL-466 :: getExoCurrentWebsiteId RETURNED ?? '.$this->configHelper->getExoCurrentWebsiteId());

            $isInternal = false;
            //SPL-409 Save Internal Orders into different card file
            if(!$this->customerHelper->isCurrentWebsiteB2B()) {
                $internalGroupName = $this->scopeConfig->getValue(
                    self::CONFIG_SETTINGS_INTERNALGROUP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $this->configHelper->getExoCurrentWebsiteId());
                $groupName = $this->customerHelper
                                ->getCustomerMageGroupById($orderData->getData()['customer_group_id'])
                                ->getCode();
                if(strtolower($internalGroupName) == strtolower($groupName)){
                    $exoOrderRequest['debtorid'] = $this->scopeConfig->getValue(
                        self::CONFIG_SETTINGS_INTERNALID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                        $this->configHelper->getExoCurrentWebsiteId());
                    $isInternal = true;
                }
            }

            if(!$isInternal){
                // debtorid, salespersonid
                $exoOrderRequest['debtorid'] = $this->scopeConfig->getValue(
                    self::CONFIG_SETTINGS_GUESTID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $this->configHelper->getExoCurrentWebsiteId()); // use this instead? what about the magento usual pushing of order?
            }

            // use config from \Acidgreen\Exo\Helper\Customer::CONFIG_DEBTOR_B2C_SALESPERSONID,
            // $exoOrderRequest['salespersonid'] = self::SALES_MOTP_ID;
            $exoOrderRequest['salespersonid'] = $this->scopeConfig->getValue(
                \Acidgreen\Exo\Helper\Customer::CONFIG_DEBTOR_B2C_SALESPERSONID,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $this->configHelper->getExoCurrentWebsiteId() // use this instead? what about the magento usual pushing of order?
            );

            /** SPL-466 - assign proper Debtor ID and Salesperson ID only for B2B */
            if (preg_match("/b2b/", $this->configHelper->getExoCurrentWebsiteId()) && !$orderData->getCustomerIsGuest()) {
                $customerModel = $this->customerModel->create()->load($orderData->getCustomerId());

                if(!$customerModel->getExoCustomerId()) {
                    throw new \Magento\Framework\Validator\Exception(__(' AX Order Error. No EXO Customer ID!'));
                }
                $exoOrderRequest['debtorid'] = $customerModel->getExoCustomerId();
                $exoOrderRequest['salespersonid'] = $customerModel->getSalesperson();

            }

            $exoOrderRequest['reference'] = $orderData->getIncrementId();
            $exoOrderRequest['istaxinclusive'] = true;

            if ($this->agCheckoutHelper->isSiteB2b()) {
                $exoOrderRequest['extrafields'][] = array(
                      'key'               => 'X_FRVALUE',
                      'value'             => null
                );
                /**
                 * SPL-421
                 */
                $exoOrderRequest['extrafields'][] = array(
                    'key' => 'X_NAV_READY',
                    'value' => self::X_NAV_READY_STATUS_NONE
                );
            } else {
                $websiteCode = $currentWebsite->getCode();

                $gstAmount = ($websiteCode == "b2c_nz_web") ? 1.15 : 1.1;

                $shippingExclGST = number_format(($orderData->getShippingAmount() / $gstAmount), 2);

                $exoOrderRequest['extrafields'][] = array(
                      'key'               => 'X_FRVALUE',
                      'value'             => $shippingExclGST
                );

                $this->logger->debug(__('SPL-412 :: Shipping Incl Tax = %1 , Excl Tax = %2, Current Website %3', $orderData->getShippingInclTax(), $shippingExclGST, $websiteCode));
            }

            if ($comment) {
                $exoOrderRequest['extrafields'][] = array(
                      'key'               => 'X_JOURNAL_MEMO',
                      'value'             => $comment
                );
            }

            /**
             * SPL-367 - Set order extra fields (especially "Recipient Name" and "Connote Name"
             */
            $exoOrderRequest = $this->setOrderExtraFields($exoOrderRequest, $orderData, $data);

            // SPL-160 - Referral keeps being set to "REP." though
            // hence, we deactivate this
            // $exoOrderRequest['extrafields'][] = array(
            //            'key'               => 'X_REFERRAL',
            //            'value'             => 'MOP'
            //        );
            $exoOrderRequest['ordertotal'] = $orderData->getTotalDue() - $orderData->getShippingInclTax();

            foreach($orderData->getAllVisibleItems() as $orderItem) {

                if(!$orderItem->getPrice()) {
                    continue;
                }

                // SPL-439 and SPL-479
                $taxIncl = (int)$this->scopeConfig->getValue(
                    self::CONFIG_SETTINGS_PRICE_INCL_TAX,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $this->configHelper->getExoCurrentWebsiteId());
                $taxPerc = $taxIncl ? $orderItem->getTaxPercent()/100.0 : 0;
                $originalPriceExclTax = round($orderItem->getBaseOriginalPrice()/(1+$taxPerc), 4);

                $exoOrderRequest['lines'][] = array(
                        'stockcode'         => $orderItem->getSku(),
                        'orderquantity'     => $orderItem->getQtyOrdered(),
                        // SPL-411 - send discount percent instead
                        'discount'          => $this->getDiscountPercent($orderItem, $orderData->getCustomerId()),
                        'unitprice'         => $originalPriceExclTax,
                        // SPL-411 - change the linetotal getting sent to EXO
                        'linetotal'         => $orderItem->getRowTotal()
                    );
            }


            $street = $orderData->getShippingAddress()->getStreet();

            /** SPL-461 hotfix */
            $exoOrderRequest['deliveryaddress'] = array(
					'line1'			=> '',
                    'line2'         => '',
                    'line3'         => '',
                    'line4'         => $orderData->getShippingAddress()->getCity(),
                    'line5'         => $orderData->getShippingAddress()->getRegion(),
                    'line6'         => $orderData->getShippingAddress()->getPostcode()
                );
            $this->logger->debug('SPL-461 :: configHelper exo current website ID? :: '.print_r([
                $this->configHelper->getExoCurrentWebsiteId(),
            ], true));

            if (!preg_match('/b2b/', $this->configHelper->getExoCurrentWebsiteId())) {
                $company = $orderData->getShippingAddress()->getCompany();
                // $exoOrderRequest['deliveryaddress']['line1'] = $streetLine;
                if (!empty($company)) {
                    /** SPL-462 **/

                    $streetLine = $street[0];
                    if (!empty($street[1])) {
                        $streetLine .= ' ';
                        $streetLine .= $street[1];
                    }

                    /** End of SPL-462 **/
                    $exoOrderRequest['deliveryaddress']['line1'] = $company;
                    $exoOrderRequest['deliveryaddress']['line2'] = $streetLine;
                } else { // SPL-462
                    /** SPL-462 **/
                    $exoOrderRequest['deliveryaddress']['line1'] = $street[0];
                    if (!empty($street[1])) {
                        $exoOrderRequest['deliveryaddress']['line2'] = $street[1];
                    }
                    /** End of SPL-462 **/
                }
            } else {
                // B2B only...
                $exoOrderRequest['deliveryaddress']['line1'] = $street[0];
                if (!empty($street[1])) {
                    $exoOrderRequest['deliveryaddress']['line2'] = $street[1];
                }
            }
            /** End of SPL-461 hotfix */

            $exoOrderRequest['stocktype'] = 'PhysicalItem';

            //need to put in extra fields
            $exoOrderRequest['status'] = '0';

            $exoOrderRequest['statusdescription'] = 'Quotation';
            $exoOrderRequest['taxtotal'] = round(($orderData->getTotalDue()-$orderData->getShippingInclTax())/11, 4);
            $exoOrderRequest['subtotal'] = round($orderData->getTotalDue()-$orderData->getShippingInclTax()-$exoOrderRequest['taxtotal'], 4);

            $this->logger->debug(print_r($exoOrderRequest, true));

            return $exoOrderRequest;

        }




    }

     /**
     * Check if Exo Sales Order Creation is Enabled
     *
     * @return Boolean
     */
    public function isExoSalesOrderCreationEnabled()
    {
        return true;
    }

    /**
     * Get store IDs under a subject website
     * @todo If not numeric, find numeric value
     * @param string $websiteId
     * @return array $storeIds
     */
//    public function getWebsiteStoreIds(string $websiteId)
//    {
//        $this->logger->debug(__('%1 :: website_id param :: %2', __METHOD__, $websiteId));
//        // If websiteId is not numeric find numeric value...
//        if (!is_numeric($websiteId)) {
//            $website = $this->websiteCollectionFactory->create();
//            $website->addFieldToFilter('code', $websiteId);
//            if ($website->count() > 0) {
//                $website = $website->getFirstItem();
//                $websiteId = $website->getId();
//            } else {
//                return [];
//            }
//
//        }
//        $this->logger->debug(__('%1 :: use this website_id :: %2', __METHOD__, $websiteId));
//
//        $storeIds = [];
//        $storeCollection = $this->storeCollectionFactory->create();
//        $storeCollection->addFieldToFilter('website_id', $websiteId);
//
//        foreach ($storeCollection as $store) {
//            $storeIds[] = $store->getStoreId();
//        }
//
//        return $storeIds;
//
//    }

    /**
     * Set necessary salesorder ExtraFields
     * @param array $exoOrderRequest
     * @param \Magento\Sales\Model\Order $orderData
     * @param array $otherData
     * @return array $exoOrderRequest
     */
    protected function setOrderExtraFields($exoOrderRequest, $orderData, $otherData)
    {
         if (!$this->agCheckoutHelper->isSiteB2b()) {
            /**
             * Set Recipient name and Connote name here..
             * Set Recipient name below
             */
            $exoOrderRequest['extrafields'][] = [
                'key' => 'X_COMMENTS',
                'value' => $this->getRecipientName($orderData->getShippingAddress())
            ];

            /**
             * Set Connote name below...
             */
            $exoOrderRequest['extrafields'][] = [
                'key' => 'X_WEB_NAME',
                'value' => $orderData->getCustomerEmail(),
            ];
        }

        return $exoOrderRequest;
    }

    private function getRecipientName($shippingAddress)
    {
        $recipientName = '';
        if (!empty($shippingAddress->getFirstname()))
            $recipientName .= $shippingAddress->getFirstname();

        if (!empty($shippingAddress->getMiddlename()))
            $recipientName .= ' ' . $shippingAddress->getMiddlename();

        if (!empty($shippingAddress->getLastname()))
            $recipientName .= ' ' . $shippingAddress->getLastname();

        $recipientName = ltrim($recipientName);

        return $recipientName;
    }

    /**
     * Get discount amount to be synced towards EXO - for an order_item
     */
    protected function getDiscountPercent($orderItem, $customerId)
    {
        /**
         * SPL-439 - Round to 4 decimal places
         */
        $taxIncl = (int)$this->scopeConfig->getValue(
            self::CONFIG_SETTINGS_PRICE_INCL_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->configHelper->getExoCurrentWebsiteId());
        $rowTotal = $taxIncl ? $orderItem->getRowTotalInclTax() : $orderItem->getRowTotal();

        $discountPercent = 1-($rowTotal-$orderItem->getDiscountAmount())/($orderItem->getBaseOriginalPrice()*$orderItem->getQtyOrdered());
        $discountPercent = $discountPercent>0.0001 ? $discountPercent : 0;
        $discountPercent = round($discountPercent, 4);
        $discountPercent *= 100;

        return $discountPercent;
    }

    protected function getTaxPercentForStore($customerId)
    {
        $store = $this->storeManager->getStore();
        $taxClassId = 2; // sorry hardcoded

        $taxPercent = $this->taxCalculation->getCalculatedRate($taxClassId, $customerId, $store->getId());

        return $taxPercent / 100.0;

    }

}
