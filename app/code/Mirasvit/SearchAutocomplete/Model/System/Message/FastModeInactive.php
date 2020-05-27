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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Model\System\Message;

use Magento\Framework\AuthorizationInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Filesystem\DirectoryList;
use Mirasvit\SearchAutocomplete\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class FastModeInactive implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var Data
     */
    protected $backendHelper;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DirectoryList
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var $engine
     */
    private $engine;

    public function __construct(
        AuthorizationInterface $authorization,
        Data                   $backendHelper,
        DirectoryList          $directoryList,
        Config                 $config,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface   $scopeConfig
    ) {
        $this->authorization = $authorization;
        $this->backendHelper = $backendHelper;
        $this->directoryList = $directoryList;
        $this->config        = $config;
        $this->objectManager = $objectManager;
        $this->scopeConfig   = $scopeConfig;
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    protected function _getAutocompleteJsonExists()
    {
        $autocompleteJsonExists = true;

        if (!file_exists($this->directoryList->getRoot().'/app/etc/autocomplete.json')) {
            $autocompleteJsonExists = false;
        }

        return $autocompleteJsonExists;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('mst_searchAutocomplete_missing_autocomplete_json');
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return !$this->_getAutocompleteJsonExists() && $this->isFastModeEnabled();
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $message = __('Mirasvit Search Autocomplete Fast Mode doesn`t work. ');
        if (!$this->externalEngineRunning()) {
            $message .= __('Fast Mode supports external engine only, please use %1.', $this->getSearchEngineData($this->engine));
        } else {
            $message .= __('Autocomplete is missing config file. ');
            $message .= __('To generate it please disable and enable again <a href="%1">fast mode</a> and run search reindex.',$this->getLink());
        }

        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->backendHelper->getUrl('admin/system_config/edit/section/searchautocomplete',[]);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }

    private function isFastModeEnabled(){
        return $this->config->isFastMode();
    }

    private function externalEngineRunning()
    {
        if ($this->getActiveSearchEngine() == 'mysql2') {
            $this->engine = $this->getAvailableExternalEngine();
            return false;
        } else {
            return true;
        }
    }

    private function getSearchEngineData($key)
    {
        $engines = [
            'mysql2'  => 'Built-in Engine',
            'sphinx'  => 'External Sphinx Engine',
            'elastic' => 'Elasticsearch Engine',
        ];

        return $engines[$key];
    }

    private function getActiveSearchEngine()
    {
        $activeEngine = 'mysql2';
        $engine = $this->scopeConfig->getValue('search/engine/engine');
        if ($engine == 'elastic') {
            try {
                $engine = $this->objectManager->create('Mirasvit\SearchElastic\Model\Engine');
                $out = '';
                $result = $engine->status($out);
                if ($result) {
                    $activeEngine = 'elastic';
                }
            } catch(\Exception $e){}
        } elseif ($engine == 'sphinx') {
            try {
                $engine = $this->objectManager->get('Mirasvit\SearchSphinx\Model\Engine');
                $out = '';
                $result = $engine->status($out);
                if ($result) {
                    $activeEngine = 'sphinx';
                }
            } catch(\Exception $e) {}
        }

        return $activeEngine;
    }

    private function getAvailableExternalEngine()
    {
        $engine = 'mysql2';
        if (class_exists('Mirasvit\SearchElastic\Model\Engine')){
            $engine = 'elastic';
        } else if (class_exists('Mirasvit\SearchSphinx\Model\Engine')) {
            $engine = 'sphinx';
        }

        return $engine;
    }
}
