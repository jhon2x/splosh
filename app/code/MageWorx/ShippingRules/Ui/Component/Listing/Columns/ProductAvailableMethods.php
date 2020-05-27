<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Ui\Component\Listing\Columns;

/**
 * Class ProductAvailableMethods
 */
class ProductAvailableMethods extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'available_shipping_methods';

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function prepareDataSource(array $dataSource)
    {
        $availableMethods = [];

        $optionsSource = $this->getData('options');
        if (empty($optionsSource) || !method_exists($optionsSource, 'toOptionArray')) {
            return $dataSource;
        }

        foreach ($optionsSource->toOptionArray() as $index => $carriers) {
            $carrierMethods = $carriers['value'] ? $carriers['value'] : [];
            foreach ($carrierMethods as $methodIndex => $carrierMethod) {
                $availableMethods[$carrierMethod['value']] = $carrierMethod['label'];
            }
        }

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (empty($item[$fieldName])) {
                    continue;
                }

                $methods = [];
                $values  = is_array($item[$fieldName]) ? $item[$fieldName] : explode(',', $item[$fieldName]);
                foreach ($values as $methodCode) {
                    if (!isset($availableMethods[$methodCode])) {
                        continue;
                    }
                    $methods[] = $availableMethods[$methodCode];
                }

                $item[$fieldName] = implode(', ', $methods);
            }
        }

        return $dataSource;
    }
}