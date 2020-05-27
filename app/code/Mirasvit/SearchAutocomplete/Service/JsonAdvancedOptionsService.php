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



namespace Mirasvit\SearchAutocomplete\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection as Resource;
use Magento\Framework\Locale\Resolver as LocaleResolver;

use Magento\Store\Model\ScopeInterface;

class JsonAdvancedOptionsService
{
    const MST_SYNONYM = 'mst_search_synonym';

    const MST_STOPWORD = 'mst_search_stopword';

    private $scopeConfig;

    private $storeManager;

    private $resource;

    private $localeResolver;

    public function __construct(
        ScopeConfigInterface    $scopeConfig,
        StoreManagerInterface   $storeManager,
        Resource                $resource,
        LocaleResolver          $localeResolver
    ) {
        $this->scopeConfig      = $scopeConfig;
        $this->storeManager     = $storeManager;
        $this->resource         = $resource;
        $this->localeResolver   = $localeResolver;
    }

    public function getWildcardSettings()
    {
        return $this->scopeConfig->getValue('search/advanced/wildcard', ScopeInterface::SCOPE_STORE);
    }

    public function getLocales()
    {
        $results = [];
        foreach ($this->storeManager->getStores() as $store) {
            $results[$store->getId()] = explode('_', $this->localeResolver->emulate($store->getId()))[0];
        }

        return $results;
    }

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

    public function getReplaceWords()
    {
        $result = [];
        try {
            $data = \Zend_Json::decode(
                $this->scopeConfig->getValue('search/advanced/replace_words')
            );
        } catch (\Exception $e) {
            $data = [];
        }

        if (is_array($data)) {
            foreach ($data as $item) {
                $from = explode(',', $item['from']);
                foreach ($from as $f) {
                    $result[$f] = trim($item['to']);
                }
            }
        }

        return $result;
    }

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

    public function getLongTail()
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

    public function getSynonyms()
    {
        $synonyms = [];
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($this->getTableData(self::MST_SYNONYM, $store->getId()) as $result) {
                $synonyms[$store->getId()][$result['term']] = $result['synonyms'];
            }
        }

        return $synonyms;
    }

    public function getStopwords()
    {
        $stopwords = [];
        $storeTerms = [];
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($this->getTableData(self::MST_STOPWORD, $store->getId()) as $result) {
                $storeTerms[] = $result['term']; 
            }

            if (!empty($storeTerms)) {
                if (empty($stopwords[$store->getId()])) {
                    $stopwords[$store->getId()] = implode (',', $storeTerms);
                } else {
                    $stopwords[$store->getId()] .= implode (',', $storeTerms);
                }
            }   
        }

        return $stopwords;
    }

    private function getTableData($tableName, $store_id, $limit = 100)
    {
        $offset = 0;
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($tableName);

        do {
            $sql = 'Select * FROM '. $tableName .' where store_id = '. $store_id . ' order by term asc limit '. $limit .' offset '. $offset ;
            $results = $connection->fetchAll($sql);

            if (count($results) > 0){
                foreach ($results as $result) {
                    yield $result;
                }
            } else {
                break;
            }

            $offset += 100;
        } while (true);
    }
}
