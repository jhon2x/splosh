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

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Service\QueryServiceInterface;
use Mirasvit\Search\Api\Service\StemmingServiceInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;
use Mirasvit\Search\Model\Config;

class QueryService implements QueryServiceInterface
{
    /**
     * @var array
     */
    private static $cache = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StopwordServiceInterface
     */
    private $stopwordService;

    /**
     * @var SynonymServiceInterface
     */
    private $synonymService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StemmingServiceInterface
     */
    private $stemmingService;

    public function __construct(
        Config $config,
        StopwordServiceInterface $stopwordService,
        SynonymServiceInterface $synonymService,
        StoreManagerInterface $storeManager,
        StemmingServiceInterface $stemmingService
    ) {
        $this->config = $config;
        $this->stopwordService = $stopwordService;
        $this->synonymService = $synonymService;
        $this->storeManager = $storeManager;
        $this->stemmingService = $stemmingService;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build($query)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if (function_exists('mb_strtolower')) {
            $query = mb_strtolower($query);
        } else {
            $query = strtolower($query);
        }

        $identifier = $storeId . $query;

        if (!array_key_exists($identifier, self::$cache)) {
            // required if synonym contains more 1 word
            $query = ' ' . $query . ' ';

            $result = [];

            $replaceWords = $this->config->getReplaceWords();

            foreach ($replaceWords as $replacement) {
                $query = str_replace(' ' . $replacement['from'] . ' ', ' ' . $replacement['to'] . ' ', $query);
            }

            $terms = preg_split('#\s#siu', $query, null, PREG_SPLIT_NO_EMPTY);

            $arSynonyms = $this->synonymService->getSynonyms($terms, $storeId);

            $condition = '$like';
            foreach ($terms as $term) {
                if (in_array($term, $this->config->getNotWords())) {
                    $condition = '$!like';
                    continue;
                }

                if ($this->stopwordService->isStopword($term, $storeId)) {
                    continue;
                }

                $wordArr = [];
                $this->addTerms($wordArr, [$term]);

                if ($condition == '$like') {
                    $this->addTerms($wordArr, [$this->applyLongTail($term)]);
                    $this->addTerms($wordArr, [$this->applyStemming($term)]);

                    if (isset($arSynonyms[$term])) {
                        # for synonyms we always disable wildcards
                        $this->addTerms($wordArr, $arSynonyms[$term], Config::WILDCARD_DISABLED);
                    }

                    if ($this->config->getMatchMode() == Config::MATCH_MODE_OR) {
                        $mode = '$or';
                    } else {
                        $mode = '$and';
                    }

                    $result[$condition][$mode][] = ['$or' => $wordArr];
                } else {
                    $result[$condition]['$and'][] = ['$and' => $wordArr];
                }
            }

            self::$cache[$identifier] = $result;
        }

        return self::$cache[$identifier];
    }

    /**
     * @param array &$to
     * @param array $terms
     * @param int|null $wildcard
     * @return void
     */
    private function addTerms(array &$to, array $terms, $wildcard = null)
    {
        $exceptions = $this->config->getWildcardExceptions();
        if ($wildcard == null) {
            $wildcard = $this->config->getWildcardMode();
        }

        foreach ($terms as $term) {
            $term = trim($term);

            if ($term == '') {
                continue;
            }

            if ($wildcard == Config::WILDCARD_PREFIX) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => Config::WILDCARD_PREFIX,
                ];
            } elseif ($wildcard == Config::WILDCARD_SUFFIX) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => Config::WILDCARD_SUFFIX,
                ];
            } elseif ($wildcard == Config::WILDCARD_DISABLED || in_array($term, $exceptions)) {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => Config::WILDCARD_DISABLED,
                ];
            } else {
                $item = [
                    '$phrase'   => $term,
                    '$wildcard' => Config::WILDCARD_INFIX,
                ];
            }

            $to[implode(array_values($item))]['$term'] = $item;
        }
    }

    /**
     * Apply long tail expression for word
     *
     * @param string $term
     *
     * @return string
     */
    private function applyLongTail($term)
    {
        $expressions = $this->config->getLongTailExpressions();

        foreach ($expressions as $expr) {
            $matches = null;
            preg_match_all($expr['match_expr'], $term, $matches);

            foreach ($matches[0] as $math) {
                $math = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                if ($math) {
                    $term = $math;
                }
            }
        }

        return $term;
    }

    /**
     * @param string $term
     * @return string
     */
    private function applyStemming($term)
    {
        return $this->stemmingService->singularize($term);
    }
}
