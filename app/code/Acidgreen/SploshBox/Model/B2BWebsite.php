<?php

namespace Acidgreen\SploshBox\Model;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;

class B2BWebsite
{
	const CONFIG_B2B_WEBSITE_ID_PATH = 'sploshbox_settings/b2b_settings/b2b_website_ids';
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	
	/**
	 * @var WebsiteCollectionFactory
	 */
	protected $websiteCollectionFactory;
	
	/**
	 * @var ConfigHelper
	 */
	protected $configHelper;
	
	/**
	 * @var WebsiteCollection
	 */
	protected $b2bWebsites;
	
	public function __construct(
		WebsiteCollectionFactory $websiteCollectionFactory,
		ConfigHelper $configHelper,
		LoggerInterface $logger
	) {
		$this->websiteCollectionFactory = $websiteCollectionFactory;
		$this->configHelper = $configHelper;
		$this->logger = $logger;
		
		$this->initB2BWebsites();
	}
	
	private function initB2BWebsites()
	{
		$collection = $this->websiteCollectionFactory->create();
		
		$b2bWebsiteIds = $this->configHelper->getScopeConfigWebsite(self::CONFIG_B2B_WEBSITE_ID_PATH);
		$b2bWebsiteIds = explode(',', $b2bWebsiteIds);
		
		$collection->addFieldToFilter('website_id', ['in' => $b2bWebsiteIds]);
		
		$this->b2bWebsites = $collection;
		
	}
	
	public function getB2BWebsites()
	{
		return $this->b2bWebsites;	
	}
}
