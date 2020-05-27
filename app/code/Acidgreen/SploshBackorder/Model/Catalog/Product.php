<?php

namespace Acidgreen\SploshBackorder\Model\Catalog;

use Magento\Framework\App\ObjectManager;

class Product extends \Magento\Catalog\Model\Product
{
    public function getIsSaleable()
    {
        $isSaleable = parent::getIsSaleable();
        return $isSaleable;
    }

    public function isSalable()
    {
        $isSalable = parent::isSalable();
        $website = $this->_storeManager->getWebsite();
        $store = $this->_storeManager->getStore();
        // B2C
        if (!preg_match("/".$website->getCode()."/", "au_web_b2b,nz_web_b2b")) {
            return $isSalable;
        }
        
        $forceBackorder = $this->getForceBackorder();

        if (empty($forceBackorder))
            $forceBackorder = $this->getResource()->getAttributeRawValue($this->getId(), 'force_backorder', $store->getId());

        if (!empty($forceBackorder)) {
            return true;
        }
        return $isSalable;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        $isAvailable = parent::isAvailable();

        $website = $this->_storeManager->getWebsite();
        $store = $this->_storeManager->getStore();
        // If B2C, return usual value...
        if (!preg_match("/".$website->getCode()."/", "au_web_b2b,nz_web_b2b")) {
            return $isAvailable;
        }
        
        /**
         * SPL-285 - To avoid displaying "Out of stock" product alert
         */
        $forceBackorder = $this->getForceBackorder();

        if (empty($forceBackorder))
            $forceBackorder = $this->getResource()->getAttributeRawValue($this->getId(), 'force_backorder', $store->getId());

        if (!empty($forceBackorder)) {
            $isAvailable = true;
        }

        return $isAvailable;
    }
}
