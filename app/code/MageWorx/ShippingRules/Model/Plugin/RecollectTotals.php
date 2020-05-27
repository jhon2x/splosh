<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class RecollectTotals
 */
class RecollectTotals
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var bool
     */
    private $totalsRecollectedFlag = false;

    /**
     * RecollectTotals constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Recollect totals each time when a payment information requested.
     * Needed to get a right result by a shipping rules in case a coupon code is applied on the checkout last step.
     *
     * @param $subject
     * @param $cartId
     * @return array
     */
    public function beforeGetPaymentInformation($subject, $cartId)
    {
        if (!$this->totalsRecollectedFlag) {
            $this->recollectTotals($cartId);
            $this->totalsRecollectedFlag = true;
        }

        return [$cartId];
    }

    /**
     * Recollect totals for the cart
     *
     * @param $cartId
     */
    private function recollectTotals($cartId)
    {
        try {
            /** @var \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote $cart */
            $cart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $exception) {
            return;
        }

        if ($cart) {
            $cart->getShippingAddress()->setCollectShippingRates(true);
            $cart->setTotalsCollectedFlag(false);
            $this->cartRepository->save($cart->collectTotals());
        }
    }
}