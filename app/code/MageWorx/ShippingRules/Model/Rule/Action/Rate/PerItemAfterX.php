<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Rule\Action\Rate;

/**
 * Class PerItemAfterX
 */
class PerItemAfterX extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        $itemsCount = count($this->validItems) - (float)$this->getCondition();

        $amountValue = $this->getAmountValue();
        if ($itemsCount > 0) {
            $resultAmountValue = $amountValue * $itemsCount;
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
