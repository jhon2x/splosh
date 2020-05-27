<?php

namespace Acidgreen\SploshBox\Model\Config\Source;

use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use Magento\Config\Model\Config\Source\Website as WebsiteOptions;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\Config\Source\Website;

class B2BWebsite extends WebsiteOptions
{
    const CONFIG_B2B_WEBSITE_ID_PATH = 'sploshbox_settings/b2b_settings/b2b_website_ids';
    
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
    	StoreManagerInterface $storeManager,
        ConfigHelper $configHelper,
        LoggerInterface $logger
    ) {
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    	parent::__construct($storeManager);
    }

    public function toOptionArray()
    {
		$options = parent::toOptionArray();
		$b2bWebsiteIds = $this->configHelper->getScopeConfigWebsite(self::CONFIG_B2B_WEBSITE_ID_PATH);
		// $b2bWebsiteIds = explode(',', $b2bWebsiteIds);
		
		$b2bWebsites = [];
		
		foreach ($options as $option) {
			$this->logger->debug(__METHOD__.' :: option value :: '.$option['value']);
			
			if (preg_match("/".$option['value']."/", $b2bWebsiteIds)) {
				$this->logger->debug(__METHOD__.' :: include this ::'.print_r($option, true));
				$b2bWebsites[] = $option;
			}
		}
			
		return $b2bWebsites;

    }
}
