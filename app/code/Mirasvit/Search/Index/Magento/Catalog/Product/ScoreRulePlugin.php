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



namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Search\Api\Service\ScoreServiceInterface;
use Mirasvit\Search\Service\ScoreRuleService;

class ScoreRulePlugin
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScoreRuleService
     */
    private $scoreRuleService;

    private static $isApplied = false;

    public function __construct(
        ResourceConnection $resource,
        ScoreRuleService $scoreRuleService
    ) {
        $this->resource = $resource;
        $this->scoreRuleService = $scoreRuleService;
    }

    /**
     * @param object $storage
     * @param Table $table
     * @return Table
     */
    public function afterStoreApiDocuments($storage, Table $table)
    {
        // apply only once for first index
        if (self::$isApplied) {
            return $table;
        }

        self::$isApplied = true;

        $this->scoreRuleService->applyScores($table);

        $select = $this->resource->getConnection()->select()
            ->from($table->getName(), ['*'])
            ->order('score desc');

        return $storage->storeDocumentsFromSelect($select);
    }
}
