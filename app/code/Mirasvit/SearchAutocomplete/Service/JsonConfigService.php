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

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mirasvit\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchAutocomplete\Model\Config;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Message\ManagerInterface;

class JsonConfigService
{
    const AUTOCOMPLETE = 'autocomplete';

    const TYPEAHEAD = 'typeahead';

    private $fs;

    private $scopeConfig;

    private $config;

    private $indexRepository;

    private $searchHelper;

    private $queryCollectionFactory;

    private $storeManager;

    private $advancedService;

    private $urlBuilder;

    public function __construct(
        Filesystem $fs,
        ScopeConfigInterface $scopeConfig,
        Config $config,
        IndexRepositoryInterface $indexRepository,
        SearchHelper $searchHelper,
        QueryCollectionFactory $queryCollectionFactory,
        StoreManagerInterface $storeManager,
        JsonAdvancedOptionsService $advancedService,
        Url $urlBuilder,
        ManagerInterface $messageManager
    ) {
        $this->fs = $fs;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->indexRepository = $indexRepository;
        $this->searchHelper = $searchHelper;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->advancedService = $advancedService;
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
   }

    /**
     * @return $this
     */
    public function ensure($option)
    {
        $path = $this->fs->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath();
        $filePath = $path . $option . '.json';

        if (!$this->isOptionEnabled($option)) {
            @unlink($filePath);

            return $this;
        }

        $config = $this->generate($option);

        if ($option == self::AUTOCOMPLETE) {
            $config['advancedConfig'] = $this->generateAutocompleteAdvancedConfig();
        }

        @file_put_contents($filePath, \Zend_Json::encode($config));

        $this->messageManager->addWarning('To avoid search autocomplete downtime please run search reindex.');

        return $this;
    }

    /**
     * @return array
     */
    public function generate($option)
    {
        switch ($option) {
            case self::AUTOCOMPLETE:
                return $this->generateAutocompleteConfig();
                break;
            case self::TYPEAHEAD:
                return $this->generateTypeaheadConfig();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @return bool
     */
    private function isOptionEnabled($option)
    {
        switch ($option) {
            case self::AUTOCOMPLETE:
                return $this->config->isFastMode();
                break;
            case self::TYPEAHEAD:
                return $this->config->isTypeAheadEnabled();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @return array
     */
    private function generateAutocompleteConfig()
    {
        $config = [
            'engine'                    => $this->scopeConfig->getValue('search/engine/engine'),
            'is_optimize_mobile'        => $this->config->isOptimizeMobile(),
            'is_show_cart_button'       => $this->config->isShowCartButton(),
            'is_show_image'             => $this->config->isShowImage(),
            'is_show_price'             => $this->config->isShowPrice(),
            'is_show_rating'            => $this->config->isShowRating(),
            'is_show_sku'               => $this->config->isShowSku(),
            'is_show_short_description' => $this->config->isShowShortDescription(),
            'textAll'                   => $this->getStoreSpecificText('Show all %1 results →', null, "%s"),
            'textEmpty'                 => $this->getStoreSpecificText('Sorry, nothing found for "%1".', null, "%s"),
            'urlAll'                    => $this->getStoreSpecificAllUrls(),
        ];

        foreach ($this->storeManager->getStores() as $store) {
            $indexes = $this->indexRepository->getIndices();
            foreach ($indexes as $index) {
                $index->addData($this->config->getIndexOptions($index->getIdentifier()));
            }
            usort($indexes, function ($a, $b) {return ($a->getOrder() < $b->getOrder()) ? -1 : 1;});

            foreach ($indexes as $index) {
                $identifier = $index->getIdentifier();

                if (!$this->config->getIndexOptionValue($identifier, 'is_active')) {
                    continue;
                }

                if ($identifier == 'magento_catalog_categoryproduct' || $identifier == 'magento_search_query') {
                    continue;
                }

                $index->addData($this->config->getIndexOptions($identifier));

                $config['indexes'][$store->getId()][$identifier] = [
                    'title'      => $this->getStoreSpecificText($index->getTitle(), $store->getId()),
                    'identifier' => $identifier,
                    'order'      => $index->getOrder(),
                    'limit'      => $index->getLimit(),
                ];
            }
        }

        return $config;
    }

    /**
     * @return array
     */
    private function generateTypeaheadConfig()
    {
        $config = [];
        $config['engine'] = false;

        $collection = $this->queryCollectionFactory->create();

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'suggest' => new \Zend_Db_Expr('MAX(query_text)'),
                'suggest_key' => new \Zend_Db_Expr('substring(query_text,1,2)'),
                'popularity' => new \Zend_Db_Expr('MAX(popularity)'),
            ])
            ->where('num_results > 0')
            ->where('display_in_terms = 1')
            ->where('is_active = 1')
            ->where('popularity > 10 ')
            ->where('CHAR_LENGTH(query_text) > 3')
            ->group(new \Zend_Db_Expr('substring(query_text,1,6)'))
            ->group(new \Zend_Db_Expr('substring(query_text,1,2)'))
            ->order('suggest_key '. \Magento\Framework\DB\Select::SQL_ASC)
            ->order('popularity ' . \Magento\Framework\DB\Select::SQL_DESC);

        foreach ($collection as $suggestion) {
            $config[strtolower($suggestion['suggest_key'])][] = strtolower($suggestion['suggest']);
        }

        return $config;
    }

    private function getStoreSpecificAllUrls()
    {
        $result = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $baseUrl = $this->storeManager->getStore($store->getId())->getBaseUrl();
            $allResultsUrl = $this->urlBuilder->getUrl('catalogsearch/result', 
                [
                    '_query' => [QueryFactory::QUERY_VAR_NAME => ''], 
                    '_secure' => false, 
                    '_scope' => $store->getId()
                ]
            );

            if (strrpos($allResultsUrl, $baseUrl) === false && strrpos($baseUrl,'/'. $storeCode .'/' ) !== false ) {
                $baseUrl = rtrim($baseUrl, '/');
                $allResultsUrlArray = explode('/',$baseUrl) + explode('/',$allResultsUrl);
                $allResultsUrl = implode('/', $allResultsUrlArray);
            }

            $result[$store->getId()] = $allResultsUrl;
        }
        
        return $result;
    }

    private function generateAutocompleteAdvancedConfig()
    {
        $config = [
            'wildcard'              => $this->advancedService->getWildcardSettings(),
            'locale'                => $this->advancedService->getLocales(),
            'wildcard_exceptions'   => $this->advancedService->getWildcardExceptions(),
            'replace_words'         => $this->advancedService->getReplaceWords(),
            'not_words'             => $this->advancedService->getNotWords(),
            'long_tail'             => $this->advancedService->getLongTail(),
            'synonyms'              => $this->advancedService->getSynonyms(),
            'stopwords'             => $this->advancedService->getStopwords(),
        ];

        return $config;
    }

    private function getStoreSpecificText($sampleText, $store = null, $parameter = null)
    {
        $result = '';

        if ($store > 0) {
            $result = $this->getEmulatedStoreText($sampleText, $store, $parameter);
        } else {
            $result = [];
            foreach ($this->storeManager->getStores() as $store) {
                $result[$store->getId()] = $this->getEmulatedStoreText($sampleText, $store->getId(), $parameter);
                //double run is required
                $result[$store->getId()] = $this->getEmulatedStoreText($sampleText, $store->getId(), $parameter);
            }
        }

        return $result;
    }

    private function getEmulatedStoreText($sampleText, int $storeId, $parameter = null)
    {
        $result = __($sampleText, $parameter)->render();
        try {
            $emulation = ObjectManager::getInstance()->get('Magento\Store\Model\App\Emulation');
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);

            $state = ObjectManager::getInstance()->get('Magento\Framework\App\State');
            $state->emulateAreaCode('frontend', function (&$result, $sampleText, $parameter) {
                $result = __($sampleText, $parameter)->render();
            }, [&$result, $sampleText, $parameter]);
        } catch (\Exception $e) {
        } finally {
            $emulation->stopEnvironmentEmulation();
        }
        
        return $result;
    }
}
