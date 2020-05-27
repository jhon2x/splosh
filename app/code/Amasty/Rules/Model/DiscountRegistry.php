<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Rules\Model\DiscountBreakdownLineFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractItem;

/**
 * Collect and store discount data for each rule or quote item
 */
class DiscountRegistry
{
    /**#@+
     * Keys for DataPersistor.
     */
    const DISCOUNT_REGISTRY_DATA = 'amasty_rules_discount_registry_data';

    const DISCOUNT_REGISTRY_SHIPPING_DATA = 'amasty_rules_discount_registry_shipping_data';
    /**#@-*/

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var DiscountBreakdownLineFactory
     */
    private $breakdownLineFactory;

    /**
     * @var bool
     */
    private $isCollect = false;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    private $currentRule;

    /**
     * Discount data storage for breakdown
     *
     * @var array ['rule_id' => ['item_id' => float]]
     */
    private $discountDataForBreakdown = [];

    /**
     * Discount data storage for debugger
     *
     * @var array ['item_id' => ['rule_id' => array]]
     */
    private $discountDataForDebugger = [];

    /**
     * Shipping discount data storage for breakdown
     *
     * @var array ['rule_id' => float]
     */
    private $shippingDiscountDataForBreakdown = [];

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        DiscountBreakdownLineFactory $breakdownLineFactory
    ) {
        $this->storeManager = $storeManager;
        $this->ruleRepository = $ruleRepository;
        $this->logger = $logger;
        $this->breakdownLineFactory = $breakdownLineFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $item
     *
     * @return $this
     */
    public function setDiscount($discountData, $rule, $item)
    {
        $this->isCollect = true;
        $this->currentRule = $rule;

        if (isset($this->discountDataForBreakdown[$rule->getId()][$item->getId()])) {
            $this->flushDiscount();
        }

        $this->discountDataForBreakdown[$rule->getId()][$item->getId()] = $discountData->getAmount();

        if ($discountData->getAmount()) {
            $this->discountDataForDebugger[$item->getId()][$rule->getId()] = [
                'amount' => $discountData->getAmount(),
                'rule_name' => $rule->getName()
            ];
        }

        $this->dataPersistor->set(self::DISCOUNT_REGISTRY_DATA, $this->discountDataForBreakdown);

        return $this;
    }

    /**
     * Restore calculated data for breakdown.
     * Return true if discountDataForBreakdown was set.
     *
     * @return bool
     */
    public function restoreDataForBreakdown()
    {
        if (!$this->discountDataForBreakdown) {
            $this->discountDataForBreakdown = $this->dataPersistor->get(self::DISCOUNT_REGISTRY_DATA) ?: [];
        }

        if (!$this->shippingDiscountDataForBreakdown) {
            $this->shippingDiscountDataForBreakdown
                = $this->dataPersistor->get(self::DISCOUNT_REGISTRY_SHIPPING_DATA) ?: [];
        }

        return !empty($this->discountDataForBreakdown);
    }

    /**
     *  Clear saved data for breakdown
     */
    public function unsetDataForBreakdown()
    {
        $this->dataPersistor->clear(self::DISCOUNT_REGISTRY_DATA);
        $this->dataPersistor->clear(self::DISCOUNT_REGISTRY_SHIPPING_DATA);
    }

    /**
     * Return amount of discount for each rule
     *
     * @return \Amasty\Rules\Api\Data\DiscountBreakdownLineInterface[]
     */
    public function getRulesWithAmount()
    {
        $totalAmount = [];
        $shippingDiscountDataForBreakdown = $this->getShippingDiscountDataForBreakdown();

        try {
            foreach ($this->getDiscount() as $ruleId => $ruleItemsAmount) {
                /** @var \Magento\SalesRule\Api\Data\RuleInterface $rule */
                $rule = $this->ruleRepository->getById($ruleId);
                $ruleAmount = array_sum($ruleItemsAmount);

                if (isset($shippingDiscountDataForBreakdown[$ruleId])) {
                    $ruleAmount += $shippingDiscountDataForBreakdown[$ruleId];
                }

                if ($ruleAmount > 0) {
                    $breakdownLine = $this->breakdownLineFactory->create();

                    if ($this->getRuleStoreLabel($rule)) {
                        $breakdownLine->setRuleName($this->getRuleStoreLabel($rule));
                    } else {
                        $breakdownLine->setRuleName($rule->getName());
                    }

                    $ruleAmount = $this->storeManager->getStore()->getCurrentCurrency()->format($ruleAmount, [], false);
                    $breakdownLine->setRuleAmount('-' . $ruleAmount);

                    $totalAmount[] = $breakdownLine;
                }
            }
        } catch (NoSuchEntityException $entityException) {
            $this->logger->critical($entityException);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
        }

        return $totalAmount;
    }

    /**
     * @param \Magento\SalesRule\Api\Data\RuleInterface $rule
     *
     * @return null|string
     *
     * @throws NoSuchEntityException
     */
    private function getRuleStoreLabel($rule)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $storeLabel = $storeLabelDefault = null;

        /* @var $label \Magento\SalesRule\Model\Data\RuleLabel */
        foreach ($rule->getStoreLabels() as $label) {
            if ($label->getStoreId() === 0) {
                $storeLabelDefault = $label->getStoreLabel();
            }

            if ($label->getStoreId() == $storeId) {
                $storeLabel = $label->getStoreLabel();
                break;
            }
        }

        $storeLabel = $storeLabel ?: $storeLabelDefault;

        return $storeLabel;
    }

    /**
     * @return array
     */
    public function getDiscount()
    {
        return $this->discountDataForBreakdown;
    }

    /**
     * @return array
     */
    public function getDiscountDataForDebugger()
    {
        return $this->discountDataForDebugger;
    }

    /**
     * @return array
     */
    public function getShippingDiscountDataForBreakdown()
    {
        return $this->shippingDiscountDataForBreakdown;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function updateQuoteData($quote)
    {
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        return $this;
    }

    /**
     * @return $this
     */
    public function flushDiscount()
    {
        $this->discountDataForBreakdown = [];
        $this->discountDataForDebugger = [];
        $this->unsetDataForBreakdown();

        return $this;
    }

    /**
     * Convert each discount breakdown line to array
     *
     * @param \Amasty\Rules\Api\Data\DiscountBreakdownLineInterface[] $rulesWithDiscount
     * @return array
     */
    public function convertRulesWithDiscountToArray($rulesWithDiscount)
    {
        $rulesWithDiscountArray = [];

        foreach ($rulesWithDiscount as $ruleWithDiscount) {
            $rulesWithDiscountArray[] = $ruleWithDiscount->__toArray();
        }

        return $rulesWithDiscountArray;
    }

    /**
     * @return bool
     */
    public function isCollect()
    {
        return $this->isCollect;
    }

    /**
     * Fix discount data if discount more than item price
     *
     * @param DiscountData $discountData
     * @param AbstractItem $item
     */
    public function fixDiscount($discountData, $item)
    {
        $discountDebuggerAmount = 0;
        $itemId = $item->getId();
        $ruleId = $this->currentRule->getRuleId();

        if (isset($this->discountDataForDebugger[$itemId][$ruleId])) {
            foreach ($this->discountDataForDebugger[$itemId] as $discountItem) {
                $discountDebuggerAmount += $discountItem['amount'];
            }

            $diff = $discountDebuggerAmount - $discountData->getAmount();
            $this->discountDataForDebugger[$itemId][$ruleId]['amount'] -= $diff;

            if ($this->discountDataForDebugger[$itemId][$ruleId]['amount'] <= 0) {
                unset($this->discountDataForDebugger[$itemId][$ruleId]);
            }

            $this->discountDataForBreakdown[$ruleId][$itemId] -= $diff;

            $this->dataPersistor->set(self::DISCOUNT_REGISTRY_DATA, $this->discountDataForBreakdown);
        }
    }

    /**
     * Calculate shipping discount amount for each sales rule
     *
     * @param string|int $ruleId
     * @param int|float $shippingDiscountAmount
     */
    public function setShippingDiscount($ruleId, $shippingDiscountAmount)
    {
        if ($this->shippingDiscountDataForBreakdown) {
            $shippingDiscount = array_sum($this->shippingDiscountDataForBreakdown);
            $this->shippingDiscountDataForBreakdown[$ruleId] = $shippingDiscountAmount - $shippingDiscount;
        } else {
            $this->shippingDiscountDataForBreakdown[$ruleId] = $shippingDiscountAmount;
        }

        $this->dataPersistor->set(self::DISCOUNT_REGISTRY_SHIPPING_DATA, $this->shippingDiscountDataForBreakdown);
    }
}
