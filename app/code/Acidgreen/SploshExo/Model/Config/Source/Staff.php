<?php

namespace Acidgreen\SploshExo\Model\Config\Source;

use Acidgreen\SploshExo\Model\ResourceModel\Staff\CollectionFactory as StaffCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Psr\Log\LoggerInterface;

class Staff extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

  	public function __construct(
      StaffCollectionFactory $collectionFactory,
      StoreManagerInterface $storeManager,
      ConfigHelper $configHelper,
      WebsiteRepositoryInterface $websiteRepository,
      LoggerInterface $logger,
      // SPL-357
      \Magento\Framework\App\State $state
  	) {
  		$this->collectionFactory = $collectionFactory;
  		$this->_storeManager = $storeManager;
  		$this->configHelper = $configHelper;
  		$this->websiteRepository = $websiteRepository;
  		$this->logger = $logger;
        // SPL-357
        $this->state = $state;
  	}


  	/**
  	 * Load options
  	 * @return array
  	 */
  	public function getAllOptions()
  	{
    		$staff = array();

    		$collection = $this->collectionFactory->create();
            /**
             * SPL-357 - filter by website ID?
             */
            $areaCode = $this->state->getAreaCode();
            // not crontab AND admin or adminhtml?
            $notInFollowingAreaCodes = ['admin', 'adminhtml'];

            if (!in_array($areaCode, $notInFollowingAreaCodes)) {
                /**
                 * SPL-357 - retrieve correct Staff on customer sync
                 */
                $websiteId = $this->configHelper->getExoCurrentWebsiteId();
                if (preg_match('/b2b/', $websiteId)) {
                    $websiteIds = \Acidgreen\SploshExo\Helper\Api\Config::B2B_WEBSITE_CODES_IDS;

                    if (!is_numeric($websiteId)) {
                        $websiteId = $websiteIds[$websiteId];
                    }

                    $collection->addFieldToFilter('website_id', $websiteId);
                }
            }

    		//$collection->addFieldToFilter('is_active', true);
    		//$collection->addFieldToFilter('website_id', $currentWebsiteId);

        $staff[] = [
            'label' => '-Select Box-',
            'value' => '',
        ];
    		if ($collection->count() > 0) {
      			foreach ($collection as $salesperson) {
        				$staff[] = [
          					'label' => $salesperson->getName(),
          					'value' => $salesperson->getExoStaffId()
        				];
      			}
    		}
    		$this->_options = $staff;
    		return $this->_options;
  	}

    /**
    * Returns data in key => value format
    * @return array
    */
    public function getScalarData() {
        $staff = array();

        $collection = $this->collectionFactory->create();

        /**
         * SPL-357 - retrieve correct Staff on customer sync
         */
        $websiteId = $this->configHelper->getExoCurrentWebsiteId();
        if (preg_match('/b2b/', $websiteId)) {
            $websiteIds = \Acidgreen\SploshExo\Helper\Api\Config::B2B_WEBSITE_CODES_IDS;
            if (!is_numeric($websiteId)) {
                $websiteId = $websiteIds[$websiteId];
            }
            $collection->addFieldToFilter('website_id', $websiteId);
        }


        if ($collection->count() > 0) {
      			foreach ($collection as $salesperson) {
        				$staff[$salesperson->getExoStaffId()] = $salesperson->getName();
      			}
    		}
        return $staff;
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
