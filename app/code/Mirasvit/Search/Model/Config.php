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



namespace Mirasvit\Search\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const CONFIG_ENGINE_PATH = 'search/engine/engine';

    const WILDCARD_INFIX = 'infix';
    const WILDCARD_SUFFIX = 'suffix';
    const WILDCARD_PREFIX = 'prefix';
    const WILDCARD_DISABLED = 'disabled';

    const MATCH_MODE_AND = 'and';
    const MATCH_MODE_OR = 'or';

    const MIN_COLLECTION_SIZE = 5;

    const DISALLOWED_MULTIPLE = ['catalogsearch_fulltext'];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * Search engine
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->scopeConfig->getValue('search/engine/engine', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Store Search engine
     *
     * @return string
     */
    public function getStoreEngine()
    {
        return $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Long tail expressions
     *
     * @return array
     */
    public function getLongTailExpressions()
    {
        try {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/long_tail_expressions', ScopeInterface::SCOPE_STORE)
            );
        } catch (\Exception $e) {
            $data = [];
        }

        if (is_array($data)) {
            return array_values($data);
        }

        return [];
    }

    /**
     * Replace words
     *
     * @return array
     */
    public function getReplaceWords()
    {
        try {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/replace_words', ScopeInterface::SCOPE_STORE)
            );
        } catch (\Exception $e) {
            $data = [];
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $from = explode(',', $item['from']);

                foreach ($from as $f) {
                    $result[] = [
                        'from' => trim($f),
                        'to'   => trim($item['to']),
                    ];
                }
            }

            return $result;
        }

        return [];
    }

    /**
     * Not words
     *
     * @return array
     */
    public function getNotWords()
    {
        $result = [];
        try {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/not_words', ScopeInterface::SCOPE_STORE)
            );
        } catch (\Exception $e) {
            $data = [];
        }

        if (is_array($data)) {
            foreach ($data as $row) {
                $result[] = $row['exception'];
            }
        }

        return $result;
    }

    /**
     * Wildcard mode
     *
     * @return string
     */
    public function getWildcardMode()
    {
        return $this->scopeConfig->getValue('search/advanced/wildcard', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Match mode
     *
     * @return string
     */
    public function getMatchMode()
    {
        return $this->scopeConfig->getValue('search/advanced/match_mode', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Wildcard exceptions
     *
     * @return array
     */
    public function getWildcardExceptions()
    {
        $result = [];
        try {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/wildcard_exceptions', ScopeInterface::SCOPE_STORE)
            );
        } catch (\Exception $e) {
            $data = [];
        }

        if (is_array($data) && !empty($data)) {
            foreach ($data as $row) {
                $result[] = $row['exception'];
            }
        }

        return $result;
    }

    /**
     * Is 404 to search enabled?
     *
     * @return bool
     */
    public function isNorouteToSearchEnabled()
    {
        return $this->scopeConfig->getValue('search/advanced/noroute_to_search', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Redirect for single results
     *
     * @return bool
     */
    public function isRedirectOnSingleResult()
    {
        return $this->scopeConfig->getValue('search/advanced/redirect_on_single_result', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Highlighter
     *
     * @return bool
     */
    public function isHighlightingEnabled()
    {
        return $this->scopeConfig->getValue('search/advanced/terms_highlighting', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Google snippet
     *
     * @return bool
     */
    public function isGoogleSitelinksEnabled()
    {
        return $this->scopeConfig->getValue('search/advanced/google_sitelinks', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Is multi-store search results mode enabled
     *
     * @return bool
     */
    public function isMultiStoreModeEnabled()
    {
        return $this->scopeConfig->getValue('search/multi_store_mode/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getEnabledMultiStores()
    {
        return explode(
            ',',
            $this->scopeConfig->getValue('search/multi_store_mode/stores', ScopeInterface::SCOPE_STORE)
        );
    }

    /**
     * @return int
     */
    public function getResultsLimit()
    {
        $limit = (int)$this->scopeConfig->getValue('search/advanced/results_limit', ScopeInterface::SCOPE_STORE);
        if (!$limit) {
            $limit = 100000;
        }

        return $limit;
    }

    /**
     * Stopwords paths
     *
     * @return string Full path to directory with stopwords
     */
    public function getStopwordDirectoryPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('sphinx/stopwords');
    }

    /**
     * Synonyms path
     *
     * @return string Full path to directory with synonyms
     */
    public function getSynonymDirectoryPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
            ->getAbsolutePath('sphinx/synonyms');
    }

    public function isFastMode()
    {
        return $this->scopeConfig->isSetFlag('searchautocomplete/general/fast_mode');
    }
}
