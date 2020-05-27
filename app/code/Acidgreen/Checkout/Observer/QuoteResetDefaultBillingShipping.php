<?php

namespace Acidgreen\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Session as CustomerSession;

class QuoteResetDefaultBillingShipping implements ObserverInterface
{
	/**
	 * @var Quote
	 */
	protected $quote;
	
	/**
	 * @var AddressRepositoryInterface
	 */
	protected $addressRepository;
	
	/**
	 * @var CustomerSession
	 */
	protected $_customerSession;
	
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
    	AddressRepositoryInterface $addressRepository,
    	CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
    	$this->addressRepository = $addressRepository;
    	$this->_customerSession = $customerSession;
        $this->logger = $logger;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();

        $this->logger->debug(__METHOD__.' :: SPL-263 OBSERVER gettype of getQuote ::'.print_r(gettype($cart->getQuote()), true));

        $this->quote = $cart->getQuote();
        
        
        if (!$this->quote)
        	return;
        
        /**
         * SPL-261:
         * Set customer_address_id to default billing/shipping address ID
         * If customer_address_id is not found anymore in customer_address_entity
         * Fix patterned after https://gist.github.com/cherreman/e35590981bf8a53a086a66c8a4f86fad
         */
        $this->useDefaultShippingAddressIfQuoteShippingAddressNotFound();
        $this->useDefaultBillingAddressIfQuoteBillingAddressNotFound();
        
        return;
    }
    
    private function useDefaultBillingAddressIfQuoteBillingAddressNotFound()
    {
    	if ($this->quote->getBillingAddress())
    	{
    		try {
    			$this->addressRepository->getById($this->quote->getBillingAddress()->getCustomerAddressId());
    		} catch (NoSuchEntityException $e) {
    			
    			$customer = $this->_customerSession->getCustomer();
    			
    			if ($customer->getDefaultBillingAddress()) {
    				
    				$this->quote->getBillingAddress()->importCustomerAddressData($customer->getDefaultBillingAddress()->getDataModel())->save();
    			}
    		}
    	}
    	
    }
    
    private function useDefaultShippingAddressIfQuoteShippingAddressNotFound()
    {

    	if ($this->quote->getShippingAddress())
    	{
    		try {
    			$this->addressRepository->getById($this->quote->getShippingAddress()->getCustomerAddressId());
    		} catch (NoSuchEntityException $e) {
    			
    			$customer = $this->_customerSession->getCustomer();
    			
    			if ($customer->getDefaultShippingAddress()) {
    				$this->quote->getShippingAddress()->importCustomerAddressData($customer->getDefaultShippingAddress()->getDataModel())->save();
    			}
    		}
    	}
    }
}
