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

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Search\Api\Repository\ScoreRuleRepositoryInterface;
use Mirasvit\Search\Model\ScoreRule\Indexer\ScoreRuleIndexer;

class ScoreRuleService
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScoreRuleRepositoryInterface
     */
    private $scoreRuleRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        ScoreRuleRepositoryInterface $scoreRuleRepository,
        RequestInterface $request
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->scoreRuleRepository = $scoreRuleRepository;
        $this->request = $request;
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Zend_Db_Exception
     */
    public function applyScores(Table $table)
    {
        $connection = $this->resource->getConnection();

        if (!$connection->isTableExists($this->getIndexTable())) {
            return $table;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $storeIds = [0, $storeId];

        $ruleIds = [0];// include Search Weight Virtual Rule

        foreach ($this->getApplicableScoreRules() as $scoreRule) {
            $ruleIds[] = $scoreRule->getId();
        }

        $select = $connection->select()->from(['index' => $this->getIndexTable()], ['*'])
            ->joinLeft(['data' => $table->getName()], 'index.product_id = data.entity_id', [])
            ->where('data.entity_id > ?', 0)
            ->where('index.store_id IN (?)', $storeIds)
            ->where('index.rule_id IN (?)', $ruleIds);

        $rows = $connection->fetchAll($select);

        $actions = [];
        foreach ($rows as $row) {
            $scoreFactor = $row[ScoreRuleIndexer::SCORE_FACTOR];
            if ($scoreFactor === '+0') {
                continue;
            }

            $actions[$scoreFactor][] = $row[ScoreRuleIndexer::PRODUCT_ID];
        }

        foreach ($actions as $action => $productIds) {
            $productIds = array_filter($productIds);

            $this->leadTo100($table);

            $connection->update(
                $table->getName(),
                ['score' => new \Zend_Db_Expr("score" . $action)],
                ['entity_id IN (' . implode(',', $productIds) . ')']
            );
        }

        return $table;
    }

    /**
     * @return ScoreRuleInterface[]
     */
    private function getApplicableScoreRules()
    {
        $result = [];
        $storeId = $this->storeManager->getStore()->getId();

        $scoreRules = $this->scoreRuleRepository->getCollection()
            ->addFieldToFilter(ScoreRuleInterface::IS_ACTIVE, 1);

        /** @var ScoreRuleInterface $scoreRule */
        foreach ($scoreRules as $scoreRule) {
            if (!in_array($storeId, $scoreRule->getStoreIds())) {
                continue;
            }

            if ($scoreRule->getActiveFrom() && strtotime($scoreRule->getActiveFrom()) > time()) {
                continue;
            }

            if ($scoreRule->getActiveTo() && strtotime($scoreRule->getActiveTo()) < time()) {
                continue;
            }

            $rule = $scoreRule->getRule();
            $obj = new \Mirasvit\Search\Model\ScoreRule\DataObject();
            $obj->setData([
                'query' => $this->request->getParam('q'),
            ]);

            if (!$rule->getPostConditions()->validate($obj)) {
                continue;
            }

            $result[] = $scoreRule;
        }

        return $result;
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function leadTo100(Table $table)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($table->getName(), [new \Zend_Db_Expr('MAX(score)')]);

        $maxScore = $connection->fetchOne($select);

        $connection->update($table->getName(), ['score' => new \Zend_Db_Expr("score / $maxScore * 100")]);

        return $table;
    }

    /**
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }
}
