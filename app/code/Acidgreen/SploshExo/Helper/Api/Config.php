<?php

namespace Acidgreen\SploshExo\Helper\Api;

use Acidgreen\Exo\Helper\Api\Config as ExoConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry as CoreRegistry;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry;

class Config extends ExoConfigHelper
{
    const CONFIG_B2B_WEBSITE_CODES = 'acidgreen_exo_apisettings/sync_settings/b2b_website_codes';

    const B2B_WEBSITE_CODES_IDS = ['au_web_b2b' => 5, 'nz_web_b2b' => 6];

    const B2C_WEBSITE_CODES_IDS = ['base' => 1, 'b2c_nz_web' => 4];
    /**
     * __construct
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     */
    public function __construct(
      	ScopeConfigInterface $scopeConfig,
      	CoreRegistry $_coreRegistry,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        Logger $logger
    ) {
        parent::__construct(
            $scopeConfig,
          	$_coreRegistry,
            $state,
            $storeManager,
            $logger
        );
    }
}
