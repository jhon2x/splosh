<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Attribute\Source;

use MageWorx\ShippingRules\Model\Config\Source\Shipping\Methods as ShippingMethodsSource;

/**
 * Class AvailableShippingMethods
 *
 * Source model of the available_shipping_methods product's attribute
 */
class AvailableShippingMethods extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var ShippingMethodsSource
     */
    private $shippingMethodsSource;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * AvailableShippingMethods constructor.
     *
     * @param ShippingMethodsSource $shippingMethodsSource
     */
    public function __construct(
        ShippingMethodsSource $shippingMethodsSource,
        \Magento\Framework\App\ProductMetadata $productMetadata
    ) {
        $this->shippingMethodsSource = $shippingMethodsSource;
        $this->productMetadata       = $productMetadata;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $methods = $this->shippingMethodsSource->toOptionArray();

            $version = $this->productMetadata->getVersion();
            if (version_compare($version, '2.2.1', '<')) {
                $oneLevelOptions = [];
                foreach ($methods as $method) {
                    $values = $method['value'];
                    if (is_array($values)) {
                        $oneLevelOptions = array_merge($oneLevelOptions, $values);
                    } else {
                        $oneLevelOptions[] = $values;
                    }
                }
                $this->_options = $oneLevelOptions;
            } else {
                $this->_options = $methods;
            }
        }

        return $this->_options;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addValueSortToCollection($collection, $dir = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        $attrCode      = $this->getAttribute()->getAttributeCode();
        $attrId        = $this->getAttribute()->getId();
        $attrTable     = $this->getAttribute()->getBackend()->getTable();
        $linkField     = $this->getAttribute()->getEntity()->getLinkField();
        $isGlobalScope = $this->getAttribute()->isScopeGlobal();

        if ($isGlobalScope) {
            $tableName = $attrCode . '_t';
            $collection->getSelect()->joinLeft(
                [$tableName => $attrTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
                " AND {$tableName}.attribute_id='{$attrId}'" .
                " AND {$tableName}.store_id='0'",
                []
            );

            $valueExpr = $tableName . '.value';
        } else {
            $valueInFirstTable  = $attrCode . '_t1';
            $valueInSecondTable = $attrCode . '_t2';

            $collection->getSelect()->joinLeft(
                [$valueInFirstTable => $attrTable],
                "e.{$linkField}={$valueInFirstTable}.{$linkField}" .
                " AND {$valueInFirstTable}.attribute_id='{$attrId}'" .
                " AND {$valueInFirstTable}.store_id='0'",
                []
            )->joinLeft(
                [$valueInSecondTable => $attrTable],
                "e.{$linkField}={$valueInSecondTable}.{$linkField}" .
                " AND {$valueInSecondTable}.attribute_id='{$attrId}'" .
                " AND {$valueInSecondTable}.store_id='{$collection->getStoreId()}'",
                []
            );

            $valueExpr = $collection->getConnection()->getCheckSql(
                $valueInSecondTable . '.value_id > 0',
                $valueInSecondTable . '.value',
                $valueInFirstTable . '.value'
            );
        }

        $collection->getSelect()->order($valueExpr . ' ' . $dir);

        return $this;
    }
}