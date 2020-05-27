<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface DiscountBreakdownLineInterface extends ExtensibleDataInterface
{
    /**#@+
    * Constant used as key into $_data
    */
    const RULE_NAME = 'rule_name';
    const RULE_AMOUNT = 'rule_amount';
    /**#@-*/

    /**
     * @return string|null
     */
    public function getRuleName();

    /**
     * @param string $ruleName
     * @return $this
     */
    public function setRuleName($ruleName);

    /**
     * @return string
     */
    public function getRuleAmount();

    /**
     * @param string $ruleAmount
     * @return $this
     */
    public function setRuleAmount($ruleAmount);
}
