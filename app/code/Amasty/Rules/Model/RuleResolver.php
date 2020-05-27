<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Amasty\Rules\Api\Data\RuleInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;

/**
 * Class for connecting Amasty Rule with Magento SalesRule.
 */
class RuleResolver
{
    /**
     * @var RuleExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var RuleProvider
     */
    private $ruleProvider;

    public function __construct(
        RuleExtensionFactory $extensionFactory,
        MetadataPool $metadata,
        RuleProvider $ruleProvider
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->metadata = $metadata;
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Amasty\Rules\Model\Rule
     *
     * @throws \Exception
     */
    public function getSpecialPromotions($salesRule)
    {
        if (!$salesRule->hasData(RuleInterface::RULE_NAME)) {
            $extensionAttributes = $salesRule->getExtensionAttrbiutes();
            if (!$extensionAttributes) {
                $extensionAttributes = $this->extensionFactory->create();
            }
            if (!$extensionAttributes->getAmrules()) {
                $amRule = $this->ruleProvider->getAmruleByRuleId($this->getLinkId($salesRule));
                $extensionAttributes->setAmrules($amRule);
            }
            $salesRule->setExtensionAttrbiutes($extensionAttributes);

            $salesRule->setData(RuleInterface::RULE_NAME, $extensionAttributes->getAmrules());
        }

        return $salesRule->getDataByKey(RuleInterface::RULE_NAME);
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getLinkId(\Magento\Rule\Model\AbstractModel $rule)
    {
        return $rule->getDataByKey($this->getLinkField());
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getLinkField()
    {
        return $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();
    }
}
