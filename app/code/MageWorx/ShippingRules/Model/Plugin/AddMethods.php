<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreResolver;

/**
 * Class AddMethods
 */
class AddMethods
{
    /**
     * @var \MageWorx\ShippingRules\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\Collection
     */
    protected $carriersCollection;

    /**
     * @var array
     */
    protected $loadedCarriers = [];

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @param \MageWorx\ShippingRules\Model\CarrierFactory $carrierFactory
     */
    public function __construct(
        \MageWorx\ShippingRules\Model\CarrierFactory $carrierFactory,
        \MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory $collectionFactory,
        StoreResolver $storeResolver
    ) {
        $this->carrierFactory           = $carrierFactory;
        $this->carrierCollectionFactory = $collectionFactory;
        $this->storeResolver            = $storeResolver;
    }

    /**
     * @param \Magento\Framework\App\Config $subject
     * @param callable $proceed
     * @param null $path
     * @param string $scope
     * @param null $scopeCode
     * @return mixed|null
     */
    public function aroundGetValue(
        $subject,
        $proceed,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $returnValue   = $proceed($path, $scope, $scopeCode);
        $filterByStore = (int)($scopeCode !== null) || $scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if (mb_stripos($path, 'carriers') === 0) {
            $pathParts  = explode('/', $path);
            $partsCount = count($pathParts);

            // Do not process existing in the config value, because it has highest priority
            if ($partsCount > 1 && $returnValue !== null) {
                return $returnValue;
            }

            switch ($partsCount) {
                case 1:
                    $this->prepareCarriers($filterByStore);
                    $returnValue = $this->addCarriers($returnValue, $filterByStore);
                    break;
                case 2:
                    $this->prepareCarriers($filterByStore);
                    $code        = $pathParts[1];
                    $returnValue = $this->getSpecificCarrierData($code);
                    break;
                case 3:
                    $this->prepareCarriers($filterByStore);
                    $code        = $pathParts[1];
                    $param       = $pathParts[2];
                    $returnValue = $param == '' ? null : $this->getSpecificCarrierData($code, $param);
                    break;
            }
        }

        return $returnValue;
    }

    /**
     * Prepare carriers collection & load items
     *
     * @param int $filterByStore
     */
    protected function prepareCarriers($filterByStore)
    {
        if (empty($this->loadedCarriers[$filterByStore])) {
            /** @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\Collection $carriersCollection */
            $this->carriersCollection = $this->carrierCollectionFactory->create();
            if ($filterByStore) {
                $storeId = $this->storeResolver->getCurrentStoreId();
                $this->carriersCollection->addStoreFilter($storeId);
            }
            $this->loadedCarriers[$filterByStore] = $this->carriersCollection->getItems();
        }

        // In case you want to display a store specific labels for backend you can do it here
    }

    /**
     * Add all available carriers to the result
     *
     * @param mixed $returnValue
     * @param int $filterByStore
     * @return mixed
     */
    protected function addCarriers($returnValue, $filterByStore)
    {
        foreach ($this->loadedCarriers[$filterByStore] as $carrier) {
            $code = $carrier->getData('carrier_code');
            if (isset($returnValue[$code])) {
                continue;
            }

            $returnValue[$code] = $carrier->getData();
        }

        return $returnValue;
    }

    /**
     * Get all data of the carrier specified by code (carrier_code)
     * It's possible to get the specified parameter ($param) of the carrier
     *
     * @param string $code
     * @param null $param
     * @return mixed|null
     */
    protected function getSpecificCarrierData($code, $param = null)
    {
        $item = $this->carriersCollection->getItemByColumnValue('carrier_code', $code);
        if (!$item) {
            return null;
        }

        if (!$param) {
            return $item->getData();
        }

        return $item->getData($param);
    }
}
