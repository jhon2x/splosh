<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */

/**
 * @codingStandardsIgnoreFile
 * @codeCoverageIgnore
 */

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setUrlKey('simple-product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->save();

$product2 = clone $product;
$product2
    ->setId(2)
    ->setSku('simple2')
    ->setName('Simple Product2')
    ->setPrice(20)
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setUrlKey('simple-product2')
    ->save();

$product3 = clone $product;
$product3
    ->setId(3)
    ->setSku('simple3')
    ->setName('Simple Product3')
    ->setPrice(30)
    ->setMetaTitle('meta title3')
    ->setUrlKey('simple-product3')
    ->setMetaKeyword('meta keyword3')
    ->setMetaDescription('meta description3')
    ->save();

$product4 = clone $product;
$product4
    ->setId(4)
    ->setSku('simple4')
    ->setName('Simple Product4')
    ->setPrice(40)
    ->setMetaTitle('meta title4')
    ->setUrlKey('simple-product4')
    ->setMetaKeyword('meta keyword4')
    ->setMetaDescription('meta description4')
    ->save();
