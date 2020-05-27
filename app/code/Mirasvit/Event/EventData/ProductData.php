<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\EventData;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ProductCondition;

class ProductData extends Item implements EventDataInterface
{
    use ContextTrait;

    const ID = 'product_id';

    const IDENTIFIER = 'product';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return ProductCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Product');
    }

    /**v
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        /** @var \Magento\CatalogRule\Model\Rule\Condition\Product $condition */
        $condition = $this->get(\Magento\CatalogRule\Model\Rule\Condition\Product::class);
        $attributes = [
            'name' => [
                'label' => __('Name'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'qty' => [
                'label' => __('Product stock quantity'),
                'type'  => self::ATTRIBUTE_TYPE_NUMBER
            ]
        ];

        foreach ($condition->loadAttributeOptions()->getData('attribute_option') as $code => $label) {
            $attributes[$code] = [
                'label' => $label
            ];
        }

        return $attributes;
    }
}