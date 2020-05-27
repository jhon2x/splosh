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



namespace Mirasvit\SearchSphinx\Adapter\Query\Builder;

use Mirasvit\Search\Api\Service\QueryServiceInterface;
use Mirasvit\SearchSphinx\SphinxQL\Expression as QLExpression;
use Mirasvit\SearchSphinx\SphinxQL\SphinxQL;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\SearchSphinx\Adapter\Field\FieldInterface;
use Mirasvit\SearchSphinx\Adapter\Field\Resolver;
use Mirasvit\Search\Model\Config;

class Match implements QueryInterface
{
    public function __construct(
        Resolver $resolver,
        QueryServiceInterface $queryService
    ) {
        $this->resolver = $resolver;
        $this->queryService = $queryService;
    }

    /**
     * @param SphinxQL $select
     * @param RequestQueryInterface $query
     * @return SphinxQL
     */
    public function build(
        SphinxQL $select,
        RequestQueryInterface $query
    ) {
        /** @var \Magento\Framework\Search\Request\Query\Match $query */

        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }

        $resolvedFieldList = $this->resolver->resolve($fieldList);

        $fieldIds = [];
        $columns = [];
        /** @var \Mirasvit\SearchSphinx\Adapter\Field\Field $field */
        foreach ($resolvedFieldList as $field) {
            if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
                $fieldIds[] = $field->getAttributeId();
            }
            $column = $field->getColumn();
            $columns[$column] = $column;
        }

        $searchQuery = $this->queryService->build($query->getValue());
        $matchQuery = $this->compileQuery($searchQuery);

        $select->match($columns, new QLExpression($matchQuery));

        return $select;
    }

    /**
     * @param array $query
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    private function compileQuery($query)
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $like = $this->compileQuery($value);
                    if ($like) {
                        $compiled[] = '(' . $like . ')';
                    }
                    break;

                case '$!like':
                    $notLike = $this->compileQuery($value);
                    if ($notLike) {
                        $compiled[] = '!(' . $notLike . ')';
                    }
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $and = array_filter($and);
                    if ($and) {
                        $compiled[] = '(' . implode(' ', $and) . ')';
                    }
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $or = array_filter($or);
                    $or = array_slice($or, 0, 3);
                    if ($or) {
                        $compiled[] = '(' . implode(' | ', $or) . ')';
                    }
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    if (strlen($phrase) == 1) {
                        if ($value['$wildcard'] == Config::WILDCARD_DISABLED) {
                            $compiled[] = "$phrase";
                        } else {
                            $compiled[] = "$phrase*";
                        }
                        break;
                    }
                    switch ($value['$wildcard']) {
                        case Config::WILDCARD_INFIX:
                            $compiled[] = "$phrase | *$phrase*";
                            break;
                        case Config::WILDCARD_PREFIX:
                            $compiled[] = "$phrase | *$phrase";
                            break;
                        case Config::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase | $phrase*";
                            break;
                        case Config::WILDCARD_DISABLED:
                            $compiled[] = $phrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' ', $compiled);
    }

    /**
     * @param string $value
     * @return string
     */
    private function escape($value)
    {
        $pattern = '/(\+|&&|\|\||\/|!|\(|\)|\{|}|\[|]|\^|"|~|@|#|\*|\?|:|\\\)/';
        $replace = '\\\$1';
        $value   = preg_replace($pattern, $replace, $value);

        $strPattern = ['-'];
        $strReplace = $value === '-' ? ['-'] : ['\-'];
        $value      = str_replace($strPattern, $strReplace, $value);

        return $value;
    }

    //    /**
    //     * @param array    $arQuery
    //     * @param SphinxQL $select
    //     * @return string
    //     */
    //    protected function buildMatchQuery($arQuery, $select)
    //    {
    //        $query = '';
    //
    //        if (!is_array($arQuery) || !count($arQuery)) {
    //            return '*';
    //        }
    //
    //        $result = [];
    //        foreach ($arQuery as $key => $array) {
    //            if ($key == '$!like') {
    //                $result[] = '-' . $this->buildWhere($key, $array, $select);
    //            } else {
    //                $result[] = $this->buildWhere($key, $array, $select);
    //            }
    //        }
    //
    //        if (count($result)) {
    //            $query = '(' . implode(' ', $result) . ')';
    //        }
    //
    //        return $query;
    //    }
    //
    //    /**
    //     * @param string   $type
    //     * @param array    $array
    //     * @param SphinxQL $select
    //     * @return array|string
    //     */
    //    protected function buildWhere($type, $array, $select)
    //    {
    //        if (!is_array($array)) {
    //            $array = str_replace('/', '\/', $array);
    //            if (substr($array, 0, 1) == ' ') {
    //                return '(' . $select->escapeMatch($array) . ')';
    //            } else {
    //                if (strlen($select->escapeMatch($array)) <= 1) {
    //                    return '(' . $select->escapeMatch($array) . '*)';
    //                } else {
    //                    return '(*' . $select->escapeMatch($array) . '*)';
    //                }
    //            }
    //        }
    //
    //        foreach ($array as $key => $subArray) {
    //            if ($key == '$or') {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //                if (is_array($array[$key])) {
    //                    $array = '(' . implode(' | ', $array[$key]) . ')';
    //                }
    //            } elseif ($key == '$and') {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //                if (is_array($array[$key])) {
    //                    $array = '(' . implode(' ', $array[$key]) . ')';
    //                }
    //            } else {
    //                $array[$key] = $this->buildWhere($type, $subArray, $select);
    //            }
    //        }
    //
    //        return $array;
    //    }
}
