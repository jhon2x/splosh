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


// @codingStandardsIgnoreStart
namespace Mirasvit\SearchSphinx;

use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;
use Mirasvit\SearchSphinx\SphinxQL\Stemming\En;
use Mirasvit\SearchSphinx\SphinxQL\Stemming\Nl;
use Mirasvit\SearchSphinx\SphinxQL\Stemming\Ru;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/autocomplete.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/autocomplete.json';
}

if (!file_exists($configFile)) {
    return;
}

$config = \Zend_Json::decode(file_get_contents($configFile));

if ($config['engine'] !== 'sphinx') {
    return;
}

class SphinxAutocomplete
{
    private $config;
    private $locales = [];

    public function __construct(
        array $config,
        En $En,
        Nl $Nl,
        Ru $Ru
    ) {
        $this->config = $config;
        $this->locales = ['en' => $En, 'nl' => $Nl, 'ru' => $Ru];
    }

    public function process()
    {
        $result = [];
        $totalItems = 0;

        foreach ($this->config['indexes'][$this->getStoreId()] as $i => $config) {
            $identifier = $config['identifier'];
            $sphinxQL = new SphinxQL($this->getConnection());
            $metaQL = new SphinxQL($this->getConnection());

            try{
            $response = $sphinxQL
                ->select(['autocomplete','LENGTH(autocomplete) AS autocomplete_strlen',new QLExpression('weight()')])
                ->from($config['index'])
                ->match('*', $this->getQuery())
                ->where('autocomplete_strlen', '>' , 0)
                ->limit(0, $config['limit'])
                ->option('max_matches', 1000000)
                ->option('field_weights', $this->getWeights($i))
                ->option('ranker', new QLExpression("expr('sum(1/min_hit_pos*user_weight 
                    + word_count*user_weight + exact_hit*user_weight*1000 + lcs*user_weight) * 1000 + bm25')"))
                ->enqueue($metaQL->query('SHOW META'))
                ->enqueue()
                ->executeBatch();
            } catch (\Exception $e) {
                $result['noResults'] = true;
                break;
            }
            $total = $response[1][0]['Value'];
            $items = $this->mapHits($response[0], $config);

            if ($total && $items) {
                $result['indices'][] = [
                    'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                    'isShowTotals' => true,
                    'order'        => $config['order'],
                    'title'        => $config['title'],
                    'totalItems'   => $total,
                    'items'        => $items,
                ];
                $totalItems += $total;
            }
        }

        $result['query'] = $this->getQueryText();
        $result['totalItems'] = $totalItems;
        $result['noResults'] = $totalItems == 0;
        $result['textEmpty'] = sprintf($this->config['textEmpty'][$this->getStoreId()], $this->getQueryText());
        $result['textAll'] = sprintf($this->config['textAll'][$this->getStoreId()], $result['totalItems']);
        $result['urlAll'] = $this->config['urlAll'][$this->getStoreId()] . $this->getQueryText();

        return $result;
    }

    private function getConnection()
    {
        $connection = new \Mirasvit\SearchSphinx\SphinxQL\Connection();
        $connection->setParams([
                'host' => $this->config['host'],
                'port' => $this->config['port'],
            ]);

        return $connection;
    }

    private function getWeights($identifier)
    {
        $weights = [];
        foreach ($this->config['indexes'][$this->getStoreId()][$identifier]['fields'] as $f => $w) {
            $weights['`'. $f .'`'] = pow(2, $w);
        }

        return $weights;
    }

    private function getQueryText()
    {
        return filter_input(INPUT_GET,'q') != null ? filter_input(INPUT_GET,'q') : '';
    }

    private function getStoreId()
    {
        return filter_input(INPUT_GET, 'store_id') != null ? filter_input(INPUT_GET, 'store_id') : array_keys($this->config['indexes'])[0] ;
    }

    private function getLocale()
    {
        return $this->config['advancedConfig']['locale'][$this->getStoreId()];
    }

    private function getQuery()
    {
        $terms = array_filter(explode(" ", $this->getQueryText()));

        $conditions = [];
        foreach ($terms as $term) {
            $term = $this->escape(mb_strtolower($term));
            $conditions[] = $this->prepareQuery($term);
        }

        return new QLExpression(implode(" ", $conditions));
    }

    private function prepareQuery($term)
    {
        $searchTerm = [];

        if (in_array($term, $this->config['advancedConfig']['not_words'])) {
            return '!';
        }

        if (isset($this->config['advancedConfig']['stopwords'][$this->getStoreId()])) {
            if (in_array($term, explode(',', $this->config['advancedConfig']['stopwords'][$this->getStoreId()]))) {
                return ' ';
            }
        }

        if (isset($this->config['advancedConfig']['replace_words'][$term])){
            $term = $this->config['advancedConfig']['replace_words'][$term];
        }

        $searchTerm[] = $this->getWildcard($term);

        $searchTerm[] = $this->lemmatize($term);
        $searchTerm[] = $this->getLongTail($term);
        $searchTerm[] = $this->getSynonyms($term);

        $searchTerm = array_filter($searchTerm);

        $searchTerm = array_unique($searchTerm);

        return '('. implode(' | ', $searchTerm) .')';
    }

    private function getWildcard($term)
    {
        if (in_array($term, $this->config['advancedConfig']['wildcard_exceptions'])) {
            return $term;
        }

        $result = [];
        $result[] = $term;

        switch ($this->config['advancedConfig']['wildcard']) {
            case 'infix':
                if (strlen($term) > 1) {
                    $result[] = '*'. $term .'*';
                } else {
                    $result[] = $term .'*';
                }
                break;
            case 'suffix':
                $result[] = $term .'*';
                break;
            case 'prefix':
                $result[] = '*'. $term;
                break;
            default:
                break;
        }

        return implode(' | ', $result);
    }

    private function lemmatize($term)
    {
        if (array_key_exists($this->getLocale(), $this->locales)) {
            return $this->getWildcard($this->locales[$this->getLocale()]->singularize($term));
        } else {
            return '';
        }
    }

    private function getLongTail($term)
    {
        $result = [];
        if (!empty($this->config['advancedConfig']['long_tail'])) {
            foreach ($this->config['advancedConfig']['long_tail'] as $expression) {
                $matches = null;
                preg_match_all($expression['match_expr'], $term, $matches);
                foreach ($matches[0] as $match) {
                    $match = preg_replace($expression['replace_expr'], $expression['replace_char'], $match);
                    if ($match) {
                        $result[] = $this->getWildcard($match);
                    }
                }
            }
        }

        return implode(' | ', $result);
    }

    private function getSynonyms($term)
    {
        $result = [];
        if (isset($this->config['advancedConfig']['synonyms'][$this->getStoreId()])) {
            if (in_array($term, array_keys($this->config['advancedConfig']['synonyms'][$this->getStoreId()]))) {
                foreach (explode(',', $this->config['advancedConfig']['synonyms'][$this->getStoreId()][$term]) as $synonym) {
                    $result[] = $synonym;
                }
            }
        }

        return implode(' | ', $result);
    }

    private function singularize ($term, $locale)
    {

    }

    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    private function mapHits($response, $config)
    {
        $items = [];
        foreach ($response as $hit) {
            if (count($items) > $config['limit']) {
                break;
            }

            $item = [
                'name'        => null,
                'url'         => null,
                'sku'         => null,
                'image'       => null,
                'description' => null,
                'price'       => null,
                'rating'      => null,
            ];

            try {
                $item = array_merge($item, \Zend_Json::decode($hit['autocomplete']));

                $item['cart'] = [
                    'visible' => false,
                    'params'  => [
                        'action' => null,
                        'data'   => [
                            'product' => null,
                            'uenc'    => null,
                        ],
                    ],
                ];

                $items[] = $item;
            } catch (\Exception $e) {
            }
        }

        return $items;
    }
}

$result = (new SphinxAutocomplete($config, new En, new Nl, new Ru))->process();

//s start
exit(\Zend_Json::encode($result));
//s end
/** m start
return \Zend_Json::encode($result);
m end */
// @codingStandardsIgnoreEnd
