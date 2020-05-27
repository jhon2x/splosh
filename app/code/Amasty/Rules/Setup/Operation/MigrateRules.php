<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Setup\Operation;

use Amasty\Rules\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Amasty\Rules\Model\RuleFactory as AmastyRule;
use Amasty\Rules\Model\ResourceModel\RuleFactory as RuleResourceFactory;

/**
 * @since 2.0.0
 * @codingStandardsIgnoreFile
 */
class MigrateRules
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var AmastyRule
     */
    private $ruleFactory;

    /**
     * @var RuleResourceFactory
     */
    private $ruleResourceFactory;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RuleRepositoryInterface $ruleRepository,
        AmastyRule $ruleFactory,
        RuleResourceFactory $ruleResourceFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->ruleResourceFactory = $ruleResourceFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $rulesArray = [
            Data::TYPE_XY_PERCENT,
            Data::TYPE_XY_FIXED,
            Data::TYPE_XY_FIXDISC,
            Data::TYPE_AFTER_N_DISC,
            Data::TYPE_AFTER_N_FIXDISC,
            Data::TYPE_AFTER_N_FIXED,
        ];

        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            Rule::KEY_SIMPLE_ACTION,
            $rulesArray,
            'in'
        )->create();

        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rulesCollection */
        $rulesCollection = $this->ruleRepository->getList($searchCriteria);
        $rules = $rulesCollection->getItems();

        /** @var \Magento\SalesRule\Model\Data\Rule $rule */
        foreach ($rules as $rule) {
            $action = $rule->getSimpleAction();
            $discountStep = $rule->getDiscountStep();
            switch ($action) {
                case Data::TYPE_XY_PERCENT:
                    $rule->setSimpleAction(Data::TYPE_XN_PERCENT);
                    break;
                case Data::TYPE_XY_FIXED:
                    $rule->setSimpleAction(Data::TYPE_XN_FIXED);
                    break;
                case Data::TYPE_XY_FIXDISC:
                    $rule->setSimpleAction(Data::TYPE_XN_FIXDISC);
                    break;
                case Data::TYPE_AFTER_N_DISC:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_PERC);
                    $rule->setDiscountStep(1);
                    break;
                case Data::TYPE_AFTER_N_FIXDISC:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_DISC);
                    $rule->setDiscountStep(1);
                    break;
                case Data::TYPE_AFTER_N_FIXED:
                    $rule->setSimpleAction(Data::TYPE_EACH_M_AFT_N_FIX);
                    $rule->setDiscountStep(1);
                    break;
            }
            $this->ruleRepository->save($rule);
            $this->setNqtyToXYrules($rule, $discountStep);
        }
    }

    /**
     * @param \Magento\SalesRule\Model\Data\Rule $rule
     * @param int $discountStep
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function setNqtyToXYrules($rule, $discountStep)
    {
        if ($rule->getRuleId()) {
            /** @var \Amasty\Rules\Model\Rule $amastyRule */
            $amastyRule = $this->ruleFactory->create();
            /** @var \Amasty\Rules\Model\ResourceModel\Rule $ruleResource */
            $ruleResource = $this->ruleResourceFactory->create();
            $ruleResource->load($amastyRule, $rule->getRuleId(), 'salesrule_id');
            if (in_array($rule->getSimpleAction(), Data::BUY_X_GET_Y)) {
                $amastyRule->setNqty(1);
            } elseif (in_array($rule->getSimpleAction(), Data::TYPE_EACH_M_AFT_N)) {
                $amastyRule->setEachm($discountStep);
            }
            $ruleResource->save($amastyRule);
        }
    }
}
