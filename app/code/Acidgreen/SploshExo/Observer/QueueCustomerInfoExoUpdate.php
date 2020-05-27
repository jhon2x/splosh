<?php

namespace Acidgreen\SploshExo\Observer;

use Acidgreen\Exo\Observer\QueueCustomerInfoExoUpdate as ExoQueueCustomerInfoExoUpdate;
use Acidgreen\SploshExo\Helper as SploshExoHelper;


use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\Exo\Model\ToupdateRepository;
use Acidgreen\Exo\Model\Toupdate;

use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http as HttpRequest;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Acidgreen\Exo\Helper\ExoCustomer as ExoCustomerHelper;

class QueueCustomerInfoExoUpdate extends ExoQueueCustomerInfoExoUpdate
{
	/**
	 * @var SploshExoHelper\Customer
	 */
	protected $sploshExoCustomerHelper;

    public function __construct(
    	CustomerRepositoryInterface $customerRepository,
    	CustomerFactory $customerFactory,
    	ToupdateRepository $toupdateRepository,
    	ExoCustomerHelper $exoCustomerHelper,
        CoreRegistry $coreRegistry,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        SploshExoHelper\Customer $sploshExoCustomerHelper
    ) {
    	$this->sploshExoCustomerHelper = $sploshExoCustomerHelper;
    	parent::__construct(
    		$customerRepository,
    		$customerFactory,
    		$toupdateRepository,
    		$exoCustomerHelper,
    		$coreRegistry,
            $storeManager,
    		$logger
    	);
    }

    /**
     * Observer method to queue B2C customer updates for resyncing
     * SPL-466 - NOT anymore
     * @return void
     */
    public function execute(Observer $observer)
    {
    	$this->logger->debug(__METHOD__.' :: DO NOT "RUN" THIS OBSERVER ANYMORE (QueueCustomerInfoExoUpdate) -- SPL-466.');
        return;
        /*
    	if (!$this->sploshExoCustomerHelper->isCurrentWebsiteB2B())
    		parent::execute($observer);
         */
    }
}
