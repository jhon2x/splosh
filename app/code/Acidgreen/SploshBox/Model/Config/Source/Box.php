<?php

namespace Acidgreen\SploshBox\Model\Config\Source;

use Acidgreen\SploshBox\Model\ResourceModel\Box\CollectionFactory as BoxCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Psr\Log\LoggerInterface;

class Box extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
	 * 
	 * @var BoxCollectionFactory
	 */
	protected $collectionFactory;	

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;
    /**
     * @var LoggerInterface
     */
    protected $logger;
	
	public function __construct(
        BoxCollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
		ConfigHelper $configHelper,
		WebsiteRepositoryInterface $websiteRepository,
        LoggerInterface $logger
	) {
		$this->collectionFactory = $collectionFactory;
		$this->_storeManager = $storeManager;
		$this->configHelper = $configHelper;
		$this->websiteRepository = $websiteRepository;
		$this->logger = $logger;
	}
	
	
	/**
	 * Load options
	 * @return array
	 */
	public function getAllOptions()
	{
		$boxes = [];
		
        // 01/09/2016 - "Current Website ID"-agnostic now...
        /*
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();

        if (empty($currentWebsiteId))
            $currentWebsiteId = 1;
        
        if ($this->configHelper->hasExoCurrentWebsiteId()) {
        	$currentWebsiteId = $this->configHelper->getExoCurrentWebsiteId();
        	if (!is_numeric($currentWebsiteId)) {
        		$currentWebsiteId = $this->websiteCodeToId($currentWebsiteId);
        	}
        }
         */

		$collection = $this->collectionFactory->create();
		
		$collection->addFieldToFilter('is_active', true);
		// $collection->addFieldToFilter('website_id', $currentWebsiteId);

        $boxes[] = [
            'label' => '-Select Box-',
            'value' => '',
        ];
		if ($collection->count() > 0) {
			foreach ($collection as $box) {
				$boxes[] = [
					'label' => $box->getBoxType(),
					'value' => $box->getBoxType()
				];
			}
		}
		$this->_options = $boxes;
		
		
		return $this->_options;
	}
	
	/**
	 * Get a text for option value
	 *
	 * @param string|integer $value
	 * @return string|bool
	 */
	public function getOptionText($value)
	{
		foreach ($this->getAllOptions() as $option) {
			if ($option['value'] == $value) {
				return $option['label'];
			}
		}
		return false;
	}
	
	private function websiteCodeToId($websiteCode)
	{
		$website = $this->websiteRepository->get($websiteCode);
		if (!empty($website))
			return $website->getId();
		
		return 1;
	}
}
