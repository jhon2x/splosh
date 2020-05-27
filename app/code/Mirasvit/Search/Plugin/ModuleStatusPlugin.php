<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;

class ModuleStatusPlugin
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var State
     **/
    private $state;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig,
        ConfigFactory $configFactory,
        State $state
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
        $this->configFactory = $configFactory;
    }

    public function afterSaveConfig()
    {
        $configData = $this->deploymentConfig->getConfigData();
        if (isset($configData['modules']) && isset($configData['modules']['Mirasvit_Search'])) {
            if ($configData['modules']['Mirasvit_Search'] == 0) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

                $config = $this->configFactory->create();
                $config->setDataByPath('catalog/search/engine', 'mysql');
                $config->save();
            }
        }
    }
}
