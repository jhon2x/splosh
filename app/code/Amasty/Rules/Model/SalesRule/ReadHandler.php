<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\SalesRule;

use Amasty\Rules\Api\Data\RuleInterface;
use Amasty\Rules\Api\Data\RuleInterfaceFactory;
use Amasty\Rules\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var RuleInterfaceFactory
     */
    private $amRuleFactory;

    public function __construct(
        RuleInterfaceFactory $amRuleFactory,
        Rule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->amRuleFactory = $amRuleFactory;
    }

    /**
     * Fill Sales Rule extension attributes with related Special Promotions Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     *
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = $entity->getDataByKey($linkField);

        if ($ruleLinkId) {
            /** @var array $attributes */
            $attributes = $entity->getExtensionAttributes() ?: [];
            $amRule = $this->amRuleFactory->create();
            $this->ruleResource->load($amRule, $ruleLinkId, RuleInterface::KEY_SALESRULE_ID);
            $attributes[RuleInterface::EXTENSION_CODE] = $amRule;
            $entity->setData(RuleInterface::RULE_NAME, $amRule);
            $entity->setExtensionAttributes($attributes);
        }

        return $entity;
    }
}
