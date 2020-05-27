<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Amasty\Rules\Api\Data\DiscountBreakdownLineInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Object DiscountBreakdownLine.
 */
class DiscountBreakdownLine extends AbstractSimpleObject implements DiscountBreakdownLineInterface
{
    /**
     * @return string
     */
    public function getRuleName()
    {
        return $this->_get(self::RULE_NAME);
    }

    /**
     * @param string $ruleName
     * @return $this
     */
    public function setRuleName($ruleName)
    {
        $this->setData(self::RULE_NAME, $ruleName);
        return $this;
    }

    /**
     * @return string
     */
    public function getRuleAmount()
    {
        return $this->_get(self::RULE_AMOUNT);
    }

    /**
     * @param string $ruleAmount
     * @return $this
     */
    public function setRuleAmount($ruleAmount)
    {
        $this->setData(self::RULE_AMOUNT, $ruleAmount);
        return $this;
    }
}
