<?php
/**
 * Customizations to Magento_Quote module
 * Copyright (C) 2017  2018
 * 
 */

namespace Acidgreen\Quote\Plugin\Magento\Quote\Model\Cart;

class CartTotalRepository
{
    public function __construct() {
        //---------- SPL-406 - debugging ----------//
        $this->logger = new \Zend\Log\Logger;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SPL-406-CartTotalRepository-Plugin.log');
        $this->logger->addWriter($writer);
    }

    public function afterGet(
        \Magento\Quote\Model\Cart\CartTotalRepository $subject,
        $quoteTotals
    ) {
        // bring back the tax amount
        $amount = $quoteTotals->getBaseGrandTotal();

        $this->logger->debug('SPL-406 :: Acidgreen_Quote :: TAX AMOUNT :: print_r getGrandTotal, taxAmount, amount :: '.print_r([
            $quoteTotals->getGrandTotal(),
            $quoteTotals->getTaxAmount(),
            $amount
        ], true));
        
        $quoteTotals->setGrandTotal($amount);
        return $quoteTotals;
    }
}
