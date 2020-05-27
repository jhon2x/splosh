<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Api;

interface RuleProviderInterface
{
    /**
     * @param int $ruleId
     *
     * @return \Amasty\Rules\Model\Rule
     */
    public function getAmruleByRuleId($ruleId);
}
