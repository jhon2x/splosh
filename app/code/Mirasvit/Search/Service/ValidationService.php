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



namespace Mirasvit\Search\Service;

use Mirasvit\Core\Service\AbstractValidator;
use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Mirasvit\Search\Model\Config;

class ValidationService extends AbstractValidator
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Manager $moduleManager,
        ModuleListInterface $moduleList,
        Config $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleList = $moduleList;
        $this->config = $config;
    }

    public function testKnownConflicts()
    {
        $known = ['Mageworks_SearchSuite', 'Magento_Solr', 'Magento_ElasticSearch'];

        foreach ($known as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $this->addError('Please disable {0} module.', [$moduleName]);
            }
        }
    }

    public function testPossibleConflicts()
    {
        $exceptions = ['Magento_Search', 'Magento_CatalogSearch'];

        foreach ($this->moduleList->getAll() as $module) {
            $moduleName = $module['name'];

            if (in_array($moduleName, $exceptions)) {
                continue;
            }

            if (stripos($moduleName, 'mirasvit') !== false) {
                continue;
            }

            if (stripos($moduleName, 'magento') !== false) {
                continue;
            }

            if (stripos($moduleName, 'search') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $this->addWarning("Possible conflict with {0} module.", [$moduleName]);
            }
        }
    }

    public function testSearchEngine()
    {
        if ($this->config->getEngine() != $this->config->getStoreEngine()) {
            $this->addWarning("Your configuration contains different search engines .
                Please check your core_config_data table and use catalog/search/engine the same as search/engine/engine");
        }
    }
}
