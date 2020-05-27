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
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Mirasvit\Misspell\Helper\Text as TextHelper;

class Indexer
{
    /**
     * @var array
     */
    private $allowedTables = [
        'catalogsearch_fulltext',
        'mst_searchindex_',
        'catalog_product_entity_text',
        'catalog_product_entity_varchar',
        'catalog_category_entity_text',
        'catalog_category_entity_varchar',
    ];

    /**
     * @var array
     */
    private $disallowedTables = [
        'mst_searchindex_mage_catalogsearch_query',
    ];

    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Mirasvit\Misspell\Helper\Text
     */
    private $text;

    public function __construct(
        ResourceConnection $resource,
        TextHelper $textHelper
    ) {
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        $this->text = $textHelper;
    }

    /**
     * @return void
     */
    public function reindex()
    {
        $indexTable = $this->resource->getTableName('mst_misspell_index');
        $this->connection->delete($indexTable);

        foreach ($this->getTables() as $table => $columns) {
            $results = [];

            if (!count($columns)) {
                continue;
            }

            foreach ($columns as $idx => $col) {
                $columns[$idx] = '`' . $col . '`';
            }

            $select = $this->connection->select();
            $fromColumns = new \Zend_Db_Expr("CONCAT_WS(' '," . implode(',', $columns) . ") as data_index");
            $select->from($table, $fromColumns);
            $results = $this->getTablesData($select);
            $rows = [];
            foreach ($results as $word => $freq) {
                $rows[] = [
                    'keyword'   => $word,
                    'trigram'   => $this->text->getTrigram($word),
                    'frequency' => $freq / count($results),
                ];

                if (count($rows) > 1000) {
                    $this->connection->insertArray($indexTable, ['keyword', 'trigram', 'frequency'], $rows);
                    $rows = [];
                }
            }

            if (count($rows) > 0) {
                $this->connection->insertArray($indexTable, ['keyword', 'trigram', 'frequency'], $rows);
            }
        }

        $this->connection->delete($this->resource->getTableName('mst_misspell_suggest'));
    }

    /**
     * @param Select    $select
     * @param array     &$results
     * @param int       $offset
     * @return void
     */
    private function getTablesData($select, $results = [], $offset = 0)
    {
        while (true) {
            $select->limit('10000', $offset);
            $result = $this->connection->query($select);
            $rows = $result->fetchAll();
            if (!$rows) {
                return $results;
            }

            foreach ($rows as $row) {
                $data = $row['data_index'];
                if (!empty($data)) {
                    $this->split($data, $results);
                }
            }

            $offset += 10000;
        }
        return $results;
    }

    /**
     * Split string to words
     *
     * @param string $string
     * @param array &$results
     * @param int $increment
     * @return void
     */
    protected function split($string, &$results, $increment = 1)
    {
        $string = $this->text->cleanString($string);
        $words = $this->text->splitWords($string);

        foreach ($words as $word) {
            if ($this->text->strlen($word) >= $this->text->getGram()
                && !is_numeric($word)
                && $this->text->strlen($word) <= 10
            ) {
                $word = $this->text->strtolower($word);
                if (!isset($results[$word])) {
                    $results[$word] = $increment;
                } else {
                    $results[$word] += $increment;
                }
            }
        }
    }

    /**
     * List of tables that follow allowedTables, disallowedTables conditions
     *
     * @return array
     */
    protected function getTables()
    {
        $result = [];
        $tables = $this->connection->getTables();

        foreach ($tables as $table) {
            $isAllowed = false;

            foreach ($this->allowedTables as $allowedTable) {
                if (mb_strpos($table, $allowedTable) !== false) {
                    $isAllowed = true;
                }
            }

            foreach ($this->disallowedTables as $disallowedTable) {
                if (mb_strpos($table, $disallowedTable) !== false) {
                    $isAllowed = false;
                }
            }

            if (!$isAllowed) {
                continue;
            }

            $result[$table] = $this->getTextColumns($table);
        }

        return $result;
    }

    /**
     * Text columns
     *
     * @param string $table Database table name
     * @return array list of columns with text type
     */
    protected function getTextColumns($table)
    {
        $result = [];
        $allowedTypes = ['text', 'varchar', 'mediumtext', 'longtext'];
        $columns = $this->connection->describeTable($table);

        foreach ($columns as $column => $info) {
            if (in_array($info['DATA_TYPE'], $allowedTypes)) {
                $result[] = $column;
            }
        }

        return $result;
    }
}
