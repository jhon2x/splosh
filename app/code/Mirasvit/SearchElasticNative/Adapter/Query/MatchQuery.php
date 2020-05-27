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



namespace Mirasvit\SearchElasticNative\Adapter\Query;

use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\Match;
use Mirasvit\Search\Api\Service\QueryServiceInterface;
use Mirasvit\Search\Model\Config;

class MatchQuery
{
    /**
     * @var QueryServiceInterface
     */
    private $queryService;

    public function __construct(
        QueryServiceInterface $queryService
    ) {
        $this->queryService = $queryService;
    }

    /**
     * @param array $query
     * @param QueryInterface $matchQuery
     * @return array
     */
    public function build(QueryInterface $matchQuery)
    {
        $query = [];
        $searchQuery = $this->queryService->build($matchQuery->getValue());

        foreach ($matchQuery->getMatches() as $match) {
            $field = $match['field'];
            if ($field == '*') {
                continue;
            }

            $boost = isset($match['boost']) ? intval((string)$match['boost']) : 1; //sometimes boots is a Phrase
            $fields[$field] = $boost;
        }

        $query['bool']['must'][]['query_string'] = [
            'fields' => array_keys($fields),
            'query'  => $this->compileQuery($searchQuery),
        ];

        foreach ($fields as $field => $boost) {
            if ($boost > 1) {
                $qs = array_filter(explode(' ', $matchQuery->getValue()));

                foreach ($qs as $q) {
                    $query['bool']['should'][]['wildcard'][$field] = [
                        'value' => '*' . strtolower($q) . '*',
                        'boost' => pow(2, $boost),
                    ];
                }
            }
        }

        return $query;
    }

    /**
     * @param array $query
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function compileQuery($query)
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($value) . ')';
                    break;

                case '$!like':
                    $compiled[] = '(NOT ' . $this->compileQuery($value) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' AND ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' OR ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    switch ($value['$wildcard']) {
                        case Config::WILDCARD_INFIX:
                            $compiled[] = "$phrase OR *$phrase*";
                            break;
                        case Config::WILDCARD_PREFIX:
                            $compiled[] = "$phrase OR *$phrase";
                            break;
                        case Config::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase OR $phrase*";
                            break;
                        case Config::WILDCARD_DISABLED:
                            $compiled[] = $phrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    /**
     * @param string $value
     * @return string
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
