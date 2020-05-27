<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Helper;

use Amasty\RulesPro\Model\ResourceModel\Order;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Calculator.
 */
class Calculator extends AbstractHelper
{
    /**
     * @var Order
     */
    private $order;

    public function __construct(
        Context $context,
        Order $order
    ) {
        parent::__construct($context);

        $this->order = $order;
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getThisMonthTotal($customerId)
    {
        $dateFrom = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $dateTo = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('t'), date('Y')));

        $conditions[] = ['date' => ' >= "' . $dateFrom . '"'];
        $conditions[] = ['date' => ' <= "' . $dateTo . '"'];
        $conditions[] = ['state' => ' = "'. \Magento\Sales\Model\Order::STATE_COMPLETE . '"'];

        return $this->getTotals($customerId, $conditions);
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getLastMonthTotal($customerId)
    {
        $year = date('Y');
        $month = date('m');

        if (0 == $month - 1) {
            $year--;
            $month = 12;
        } else {
            $month--;
        }
        $last = mktime(0, 0, 0, $month, 1, $year);

        $from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $last), 1, date('Y', $last)));
        $dateTo = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $last), date('t', $last), date('Y', $last)));

        $conditions[] = ['date' => ' >= "' . $from . '"'];
        $conditions[] = ['date' => ' <= "' . $dateTo . '"'];
        $conditions[] = ['state' => ' = "' . \Magento\Sales\Model\Order::STATE_COMPLETE . '"'];

        return $this->getTotals($customerId, $conditions);
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getAllPeriodTotal($customerId)
    {
        return $this->getTotals($customerId);
    }

    /**
     * @param int $customerId
     * @param string $fieldName
     * @param array $conditions
     * @param string $conditionType
     *
     * @return mixed
     */
    public function getSingleTotalField($customerId, $fieldName, $conditions, $conditionType)
    {
        $result = $this->getTotals($customerId, $conditions, $conditionType);

        return isset($result[$fieldName]) ? $result[$fieldName] : false;
    }

    /**
     * Calculates aggregated order values for given customer
     *
     * @param int $customerId
     * @param array $conditions e.g. array( 0=> array('date'=>'>2013-12-04'),  1=>array('status'=>'>2013-12-04'))
     * @param string $conditionType "all"  or "any"
     *
     * @return array
     */
    private function getTotals($customerId, $conditions = [], $conditionType = Order::ALL)
    {
        return $this->order->getTotals($customerId, $conditions, $conditionType);
    }
}
