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
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Mirasvit\Search\Model\Config;

class SyncEngineConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigFactory $configFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configFactory = $configFactory;
    }

    /**
     * @return void
     */
    public function beforeGet()
    {
        $native = $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
        $our = $this->scopeConfig->getValue(Config::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);

        if ($native != $our) {
            $config = $this->configFactory->create();

            $config->setDataByPath('catalog/search/engine', $our);
            $config->save();
        }
    }
}
