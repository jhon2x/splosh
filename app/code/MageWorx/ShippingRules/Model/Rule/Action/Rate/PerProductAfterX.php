<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Rule\Action\Rate;

/**
 * Class PerProductAfterX
 */
class PerProductAfterX extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        $productQty = 0;
        $qtyLimit   = (float)$this->getCondition();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->validItems as $item) {
            if ($item->getParentItem()) {
                $qty = (float)$item->getQty() * (float)$item->getParentItem()->getQty();
            } else {
                $qty = (float)$item->getQty();
            }
            $productQty += $qty;
        }

        $amountValue = $this->getAmountValue();
        $productQty  -= $qtyLimit;
        if ($productQty > 0) {
            $resultAmountValue = $amountValue * $productQty;
        } else {
            if ($this->getApplyMethod() === 'overwrite') {
                $resultAmountValue = $this->getRate()->getPrice();
            } else {
                $resultAmountValue = 0;
            }
        }

        $this->_setAmountValue($resultAmountValue);

        return $this;
    }

    /**
     * Calculate percent of amount
     *
     * @important Impossible to calculate percent - DOES NOTHING!
     *
     * @return AbstractRate
     */
    protected function percent()
    {
        if ($this->getApplyMethod() === 'overwrite') {
            $resultAmountValue = $this->getRate()->getPrice();
        } else {
            $resultAmountValue = 0;
        }
        $this->_setAmountValue($resultAmountValue);

        return $this;
    }
}
