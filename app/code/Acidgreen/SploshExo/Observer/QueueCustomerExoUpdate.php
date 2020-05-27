<?php

namespace Acidgreen\SploshExo\Observer;

use Acidgreen\Exo\Observer\QueueCustomerExoUpdate as ExoQueueCustomerExoUpdate;
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


class QueueCustomerExoUpdate extends ExoQueueCustomerExoUpdate
{
	/**
	 * @var SploshExoHelper\Customer
	 */
	protected $sploshExoCustomerHelper;

    public function __construct(
    	ToupdateRepository $toupdateRepository,
    	StoreManagerInterface $storeManager,
    	CoreRegistry $coreRegistry,
        HttpRequest $request,
        LoggerInterface $logger,
        SploshExoHelper\Customer $sploshExoCustomerHelper
    ) {
    	$this->sploshExoCustomerHelper = $sploshExoCustomerHelper;
    	parent::__construct(
	    	$toupdateRepository,
	    	$storeManager,
	    	$coreRegistry,
	        $request,
	        $logger
    	);
    }	

    public function execute(Observer $observer)
    {

    	$this->logger->debug(__METHOD__.' :: DO NOT "RUN" THIS OBSERVER ANYMORE (QueueCustomerExoUpdate) -- SPL-466.');
        /*
    	if (!$this->sploshExoCustomerHelper->isCurrentWebsiteB2B()) {
            $this->logger->debug(__METHOD__.' :: isCurrentWebsiteB2B false :: run parent::execute($observer)');
	    	parent::execute($observer);
        }
         */

	    return;
    }
}
