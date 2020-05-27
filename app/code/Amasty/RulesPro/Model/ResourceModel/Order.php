<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Model\ResourceModel;

/**
 * Class for Data precessing from DB
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const ALL = 'all';
    const TABLE_NAME = 'sales_order';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param int $customerId
     * @param array $conditions e.g. array( 0=> array('date'=>'>2013-12-04'),  1=>array('state'=>'>2013-12-04'))
     * @param string $conditionType "all"  or "any"
     *
     * @return array
     */
    public function getTotals($customerId, $conditions, $conditionType)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['o' => $this->getTable(self::TABLE_NAME)], [])
            ->where('o.customer_id = ?', $customerId);

        $map = [
            'date' => 'o.created_at',
            'state' => 'o.state',
            'status' => 'o.status',
        ];

        foreach ($conditions as $element) {
            $value = current($element);
            $field = $map[key($element)];
            $whereCond = $field . ' ' . $value;

            if ($conditionType == static::ALL) {
                $select->where($whereCond);
            } else {
                $select->orWhere($whereCond);
            }
        }

        $select->from(
            null,
            ['count' => 'COUNT(*)', 'amount' => 'SUM(o.base_grand_total)']
        );
        $row = $connection->fetchRow($select);

        return [
            'average_order_value' => $row['count'] ? $row['amount'] / $row['count'] : 0,
            'total_orders_amount' => $row['amount'],
            'of_placed_orders' => $row['count'],
        ];
    }

    /**
     * @param int $customerId
     * @param string $attribute
     *
     * @return string
     */
    public function getValidationData($customerId, $attribute)
    {
        $connection = $this->getConnection();
        $columns = [];

        if ('order_num' == $attribute) {
            $columns = ['COUNT(*)'];
        } elseif ('sales_amount' == $attribute) {
            $columns = ['SUM(o.base_grand_total)'];
        }

        $select = $connection->select()
            ->from(['o' => $this->getTable(self::TABLE_NAME)], $columns)
            ->where('o.customer_id = ?', $customerId)
            ->where('o.state = ?', \Magento\Sales\Model\Order::STATE_COMPLETE);

        return $connection->fetchOne($select);
    }
}
