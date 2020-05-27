<?php

namespace Splosh\ExtendCatalog\Plugin\Model;

class Config
{
    /**
     * @param \Magento\Catalog\Model\Config $subject
     * @param $options
     * @return array
     */
    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $subject,
        $options
    ) {
        unset($options);
        $options = [
            'position'      => __('Default'),
            'product_asc'   => __('Product A to Z'),
            'product_desc'  => __('Product Z to A'),
            'price_asc'     => __('Price Low to High'),
            'price_desc'    => __('Price High to Low')
        ];

        return $options;
    }
}