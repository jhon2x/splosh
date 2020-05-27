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


namespace Mirasvit\Event\EventData\Condition\Product;

use Magento\Rule\Model\Condition\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as CatalogRuleProductFactory;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var CatalogRuleProductFactory
     */
    protected $catalogRuleProductFactory;

    /**
     * @param Context                   $context
     * @param CatalogRuleProductFactory $catalogRuleProductFactory
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        CatalogRuleProductFactory $catalogRuleProductFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->setData('type', self::class);

        $this->catalogRuleProductFactory = $catalogRuleProductFactory;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productCondition = $this->catalogRuleProductFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()
            ->getAttributeOption();

        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => \Magento\CatalogRule\Model\Rule\Condition\Product::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [];
        $conditions = array_merge_recursive($conditions, [
            [
                'label' => __('Conditions Combination'),
                'value' => self::class,
            ],
            [
                'label' => __('Product Attribute'),
                'value' => $attributes,
            ],
            [
                'label' => __('Additional Product Conditions'),
                'value' => [
                    [
                        'label' => __('Newest Products'),
                        'value' => \Mirasvit\Event\EventData\Condition\Product\Newest::class
                    ],
                    [
                        'label' => __('Top Selling Products'),
                        'value' => \Mirasvit\Event\EventData\Condition\Product\Topselling::class
                    ]
                ],
            ],
        ]);

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
