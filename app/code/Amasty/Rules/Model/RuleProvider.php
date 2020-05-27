<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Amasty\Rules\Api\RuleProviderInterface;
use Amasty\Rules\Model\ResourceModel\Rule as RuleResource;
use Amasty\Rules\Api\Data\RuleInterface;

/**
 * Rule Provider.
 */
class RuleProvider implements RuleProviderInterface
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var array [SalesRule_id => SpecialPromotions_Rule]
     */
    private $storage = [];

    public function __construct(
        RuleResource $ruleResource,
        RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param int $ruleId
     * @return \Amasty\Rules\Model\Rule
     */
    public function getAmruleByRuleId($ruleId)
    {
        if (!isset($this->storage[$ruleId])) {
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $ruleId, RuleInterface::KEY_SALESRULE_ID);
            $this->storage[$ruleId] = $rule;
        }

        return $this->storage[$ruleId];
    }
}
