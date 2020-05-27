<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Rule\Action\Rate;

/**
 * Class PerUnitOfWeight
 */
class PerUnitOfWeight extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        $weight = $this->getWeight();

        $amountValue       = $this->getAmountValue();
        $resultAmountValue = $amountValue * $weight;
        $this->_setAmountValue($resultAmountValue);

        return $this;
    }

    /**
     * Get all items row weight
     * Note: $item->getRowWeight() works very strangely, use a regular weight & qty instead
     *
     * @return float
     */
    protected function getWeight()
    {
        $weight = 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->validItems as $item) {
            $qty = (float)$item->getQty();
            if ($item->getParentItem() && $item->getParentItem()->getQty()) {
                $qty *= (float)$item->getParentItem()->getQty();
            }
            $weight += (float)$item->getWeight() * $qty;
        }

        return (float)$weight;
    }

    /**
     * Calculate percent of amount
     *
     * @return AbstractRate
     */
    protected function percent()
    {
        $rate        = $this->getRate();
        $amountValue = $this->getAmountValue() ? $this->getAmountValue() / 100 : 0;
        $amount      = (float)$rate->getPrice() * $amountValue;

        $weight = $this->getWeight();

        $resultAmountValue = $amount * $weight;
        $this->_setAmountValue($resultAmountValue);

        return $this;
    }
}
