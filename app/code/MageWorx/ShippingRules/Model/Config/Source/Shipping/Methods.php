<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Config\Source\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Config as ShippingConfig;

/**
 * Class Methods
 */
class Methods implements ArrayInterface
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    private $methodsAsArray = [];

    /**
     * @var array
     */
    private $methodsAsOptionArray = [];

    /**
     * @param ShippingConfig $shippingConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ShippingConfig $shippingConfig,
        ScopeConfigInterface $scopeConfig
    ) {

        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig    = $scopeConfig;
    }

    /**
     * @param bool $isActiveOnlyFlag
     * @param bool $forceRenew
     * @return array
     */
    public function toArray($isActiveOnlyFlag = false, $forceRenew = false)
    {
        $intFlag = (int)$isActiveOnlyFlag;
        if (!empty($this->methodsAsArray[$intFlag]) && !$forceRenew) {
            return $this->methodsAsArray[$intFlag];
        }

        $methodsAsOptionArray = $this->toOptionArray($isActiveOnlyFlag);
        $methodsAsArray       = [];
        foreach ($methodsAsOptionArray as $carrier) {
            if (empty($carrier['value'])) {
                continue;
            }
            $carrierMethods = $carrier['value'];
            foreach ($carrierMethods as $carrierMethod) {
                if (empty($carrierMethod['value'])) {
                    continue;
                }
                $methodsAsArray[] = $carrierMethod['value'];
            }
        }

        $this->methodsAsArray[$intFlag] = $methodsAsArray;

        return $this->methodsAsArray[$intFlag];
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $intFlag = (int)$isActiveOnlyFlag;
        if (!empty($this->methodsAsOptionArray[$intFlag])) {
            return $this->methodsAsOptionArray[$intFlag];
        }

        $carriers = $this->shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods || !is_array($carrierMethods)) {
                continue;
            }
            $carrierTitle          = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => []];
            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[$carrierCode]['value'][] = [
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => '[' . $carrierCode . '] ' . ($methodTitle ? $methodTitle : $methodCode),
                ];
            }
        }

        if (empty($methods)) {
            $methods = [
                'label' => [],
                'value' => []
            ];
        }

        $this->methodsAsOptionArray[$intFlag] = $methods;

        return $this->methodsAsOptionArray[$intFlag];
    }
}
