<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Rule\Action\Rate;

/**
 * Class Amount
 */
class Amount extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        return $this;
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
        $this->_setAmountValue($amount);

        return $this;
    }
}
