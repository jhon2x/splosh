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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Misspell\Provider;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Misspell\Helper\Text as TextHelper;
use Mirasvit\Misspell\Helper\Damerau as DamerauHelper;

class Suggester
{
    /**
     * @var array
     */
    private $diffs;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var DamerauHelper
     */
    private $damerauHelper;

    /**
     * @var TextHelper
     */
    private $textHelper;

    public function __construct(
        ResourceConnection $resource,
        TextHelper $textHelper,
        DamerauHelper $damerauHelper
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->damerauHelper = $damerauHelper;
        $this->textHelper = $textHelper;
    }

    /**
     * Return spelled string
     *
     * @param string $baseQuery
     * @return string
     */
    public function getSuggest($baseQuery)
    {
        $this->diffs = [];
        $this->keys = [];
        $final = [];

        $baseQuery = $this->textHelper->cleanString($baseQuery);
        $queries = $this->textHelper->splitWords($baseQuery);

        foreach ($queries as $query) {
            $len = $this->textHelper->strlen($query);

            if ($len < $this->textHelper->getGram() || is_numeric($query)) {
                $final[] = $query;
                continue;
            }

            $result = $this->getBestMatch($query);
            $keyword = $result['keyword'];

            $this->split($query, '', $query);
            $splitKeyword = '';

            if (count($this->diffs)) {
                arsort($this->diffs);
                $keys = array_keys($this->diffs);
                $key = $keys[0];
                $splitKeyword = $this->keys[$key];
            }

            $basePer = $this->damerauHelper->similarity($query, $keyword);
            $splitPer = $this->damerauHelper->similarity($query, $splitKeyword);

            if ($basePer > $splitPer) {
                $final[] = $keyword;
            } else {
                $final[] = $splitKeyword;
            }
        }

        $result = implode(' ', $final);

        if ($this->damerauHelper->similarity($result, $baseQuery) < 50) {
            $result = '';
        }

        return $result;
    }

    /**
     * Split string
     *
     * @param string $query
     * @param string $prefix
     * @param string $base
     * @return bool
     */
    protected function split($query, $prefix = '', $base = '')
    {
        $len = $this->textHelper->strlen($query);

        if ($len > 20) {
            return false;
        }

        for ($i = $this->textHelper->getGram(); $i <= $len - $this->textHelper->getGram() + 1; $i++) {
            $a = $this->textHelper->substr($query, 0, $i);
            $b = $this->textHelper->substr($query, $i);

            $aa = $this->getBestMatch($a);
            $bb = $this->getBestMatch($b);

            $key = $a . '|' . $b;

            if ($prefix) {
                $key = $prefix . '|' . $key;
            }

            $this->keys[$key] = '';
            if ($prefix) {
                $this->keys[$key] = $prefix . ' ';
            }
            $this->keys[$key] .= $aa['keyword'] . ' ' . $bb['keyword'];

            $this->diffs[$key] = (
                    $this->damerauHelper->similarity($base, $this->keys[$key]) + $aa['diff'] + $bb['diff']
                ) / 3;

            if ($prefix) {
                $kwd = $prefix . '|' . $aa['keyword'];
            } else {
                $kwd = $aa['keyword'];
            }

            if ($aa['diff'] > 50) {
                $this->split($b, $kwd, $query);
            }
        }

        return null;
    }

    /**
     * Return best match (from database)
     *
     * @param string $query
     * @return array
     */
    public function getBestMatch($query)
    {
        $query = trim($query);

        if (!$query) {
            return ['keyword' => $query, 'diff' => 100];
        }

        $len = (int)$this->textHelper->strlen($query);
        $trigram = $this->textHelper->getTrigram($this->textHelper->strtolower($query));

        $tableName = $this->resource->getTableName('mst_misspell_index');

        $select = $this->connection->select();
        $relevance = '(-ABS(LENGTH(keyword) - ' . $len . ') + MATCH (trigram) AGAINST("' . $trigram . '"))';
        $relevancy = new \Zend_Db_Expr($relevance . ' + frequency AS relevancy');
        $select->from($tableName, ['keyword', $relevancy, 'frequency'])
            ->order('relevancy desc')
            ->limit(10);

        try {
            $keywords = $this->connection->fetchAll($select);
        } catch (\Exception $e) {
            return ['keyword' => $query, 'diff' => 100];
        }

        $maxFreq = 0.0001;
        foreach ($keywords as $keyword) {
            $maxFreq = max($keyword['frequency'], $maxFreq);
        }

        $preResults = [];
        foreach ($keywords as $keyword) {
            $preResults[$keyword['keyword']] = $this->damerauHelper->similarity($query, $keyword['keyword'])
                + $keyword['frequency'] * (10 / $maxFreq);
        }
        arsort($preResults);

        $keys = array_keys($preResults);

        if (count($keys) > 0) {
            $keyword = $keys[0];
            $keyword = $this->toSameRegister($keyword, $query);
            $diff = $preResults[$keys[0]];
            $result = ['keyword' => $keyword, 'diff' => $diff];
        } else {
            $result = ['keyword' => $query, 'diff' => 100];
        }

        return $result;
    }

    /**
     * Convert $str to same register with $base
     *
     * @param string $str
     * @param string $base
     * @return string
     */
    protected function toSameRegister($str, $base)
    {
        $minLen = min($this->textHelper->strlen($base), $this->textHelper->strlen($str));

        for ($i = 0; $i < $minLen; $i++) {
            $chr = $this->textHelper->substr($base, $i, 1);

            if ($chr != $this->textHelper->strtolower($chr)) {
                $chrN = $this->textHelper->substr($str, $i, 1);
                $chrN = strtoupper($chrN);
                $str = substr_replace($str, $chrN, $i, 1);
            }
        }

        return $str;
    }
}
