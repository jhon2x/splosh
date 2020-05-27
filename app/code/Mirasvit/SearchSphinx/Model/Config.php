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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.49
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchSphinx\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem           $filesystem
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->scopeConfig->getValue('search/engine/host');
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return (int)$this->scopeConfig->getValue('search/engine/port');
    }

    /**
     * @return bool
     */
    public function isSameServer()
    {
        return $this->scopeConfig->getValue('search/engine/same_server') ? true : false;
    }

    /**
     * @return array
     */
    public function getBinPath()
    {
        return array_map('trim', explode(',', $this->scopeConfig->getValue('search/engine/bin_path')));
    }

    /**
     * @return bool
     */
    public function isAutoRestartAllowed()
    {
        return $this->scopeConfig->getValue('search/engine/auto_restart') ? true : false;
    }

    /**
     * @return string
     */
    public function getCustomBasePath()
    {
        return $this->scopeConfig->getValue('search/engine/extended/custom_base_path');
    }

    /**
     * @return string
     */
    public function getAdditionalSearchdConfig()
    {
        return $this->scopeConfig->getValue('search/engine/extended/custom_searchd');
    }

    /**
     * @return string
     */
    public function getAdditionalIndexConfig()
    {
        return $this->scopeConfig->getValue('search/engine/extended/custom_index');
    }

    /**
     * @return string
     */
    public function getCustomCharsetTable()
    {
        return $this->scopeConfig->getValue('search/engine/extended/custom_charset_table');
    }

    /**
     * @return string
     */
    public function getSphinxConfigurationTemplate()
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/sphinx.conf';

        return file_get_contents($path);
    }

    /**
     * @return string
     */
    public function getSphinxIndexConfigurationTemplate()
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/index.conf';

        return file_get_contents($path);
    }

    /**
     * @return string
     */
    public function getDefaultCharsetTable()
    {
        $path = dirname(dirname(__FILE__)) . '/etc/conf/charset.conf';

        return file_get_contents($path);
    }

    /**
     * @return bool
     */
    public function isFastMode()
    {
        return $this->scopeConfig->isSetFlag('searchautocomplete/general/fast_mode');
    }
}
