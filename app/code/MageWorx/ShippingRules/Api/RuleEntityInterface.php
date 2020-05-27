<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api;

interface RuleEntityInterface
{
    /**
     * Get store specific error message
     *
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method|\Magento\Quote\Model\Quote\Address\Rate $rate
     * @param null $storeId
     * @return mixed
     */
    public function getStoreSpecificErrorMessage(
        \Magento\Framework\DataObject $rate,
        $storeId = null
    );
}
