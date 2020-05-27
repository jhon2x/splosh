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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as ProductConditionFactory;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Event\EventData\OrderData;
use Mirasvit\Event\EventData\QuoteData;

class Different extends \Magento\Rule\Model\Condition\Combine
{
    const OPERATOR_SAME = '=';
    const OPERATOR_DIFF = '!=';
    const VALUE_OPERATOR_MORE = '>';
    const VALUE_OPERATOR_LESS = '<';
    const VALUE_OPERATOR_EQUAL = '==';
    const VALUE_OPERATOR_ALL = 'all';
    const AGGREGATOR_ALL = 'all';
    const AGGREGATOR_ANY = 'any';
    /**
     * @var ProductCondition
     */
    private $productCondition;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Different constructor.
     *
     * @param StoreManagerInterface   $storeManager
     * @param OrderFactory            $orderFactory
     * @param QuoteFactory            $quoteFactory
     * @param ProductConditionFactory $productConditionFactory
     * @param Context                 $context
     * @param array                   $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        ProductConditionFactory $productConditionFactory,
        Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->productCondition = $productConditionFactory->create();

        parent::__construct($context, $data);

        $this->loadValueOperatorOptions();

        $this->setData('type', self::class);
        $this->setData('operator', self::OPERATOR_DIFF);
    }

    /**
     * Get product attributes.
     *
     * @return string[]
     */
    private function getProductAttributes()
    {
        return $this->productCondition->loadAttributeOptions()->getData('attribute_option');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewChildSelectOptions()
    {
        $attributes = array();
        foreach ($this->getProductAttributes() as $code => $label) {
            $attributes[] = ['value' => ProductCondition::class.'|'.$code, 'label' => $label];
        }

        $conditions = array();
        $conditions = array_merge_recursive($conditions, [
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
    public function loadAttributeOptions()
    {
        $this->setData('attribute_option', $this->getProductAttributes());

        return $this;
    }

    /**
     * Add attribute when loading array
     *
     * @param array $arr
     * @param string $key
     *
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        if (isset($arr['operator'])) {
            $this->setData('operator', $arr['operator']);
        }

        if (isset($arr['attribute'])) {
            $this->setData('attribute', $arr['attribute']);
        }

        if (isset($arr['value_operator'])) {
            $this->setData('value_operator', $arr['value_operator']);
        }

        return parent::loadArray($arr, $key);
    }

    public function loadOperatorOptions()
    {
        $this->setData('operator_option', array(
            self::OPERATOR_SAME => __('Same'),
            self::OPERATOR_DIFF => __('Different'),
        ));

        return $this;
    }

    public function loadValueOptions()
    {
        $this->setData('value_option', array());
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function loadValueOperatorOptions()
    {
        $this->setData('value_operator_option', array(
            self::VALUE_OPERATOR_EQUAL => __('Equal To'),
            self::VALUE_OPERATOR_MORE  => __('More Than'),
            self::VALUE_OPERATOR_LESS  => __('Less Than'),
            self::VALUE_OPERATOR_ALL   => __('All'),
        ));

        return $this;
    }

    public function getValueOperatorName()
    {
        return $this->getData('value_operator_option', $this->getData('value_operator'));
    }

    /**
     * Retrieve Condition Operator element Instance
     * If the operator value is empty - define first available operator value as default
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getValueOperatorElement()
    {
        $options = $this->getData('value_operator_option');
        if (is_null($this->getData('value_operator'))) {
            foreach ($options as $key => $option) {
                $this->setData('value_operator', $key);
                break;
            }
        }

        $elementId   = sprintf('%s__%s__value_operator', $this->getData('prefix'), $this->getData('id'));
        $elementName = sprintf('%s[%s][%s][value_operator]',
            $this->elementName,
            $this->getData('prefix'),
            $this->getData('id')
        );

        $element = $this->getForm()->addField($elementId, 'select', array(
                'name'          => $elementName,
                'values'        => $options,
                'value'         => $this->getData('value_operator'),
                'value_name'    => $this->getValueOperatorName(),
            ))
            ->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));

        return $element;
    }

    /**
     * {@inheritDoc}
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
        . __('If %1 %2 of %3 %4 found while %5 of these conditions match:',
            $this->getValueOperatorElement()->getHtml(),
            $this->getValueElementHtml(),
            $this->getOperatorElementHtml(),
            $this->getAttributeElementHtml(),
            $this->getAggregatorElement()->getHtml()
        )
        . $this->getRemoveLinkHtml();
    }

    /**
     * {@inheritDoc}
     */
    public function validate(AbstractModel $object)
    {
        $isValid    = true;
        $value      = $this->getValue();
        $operator   = $this->getData('value_operator');

        if (count($this->getCollection($object))) {
            $values = $this->collectValues($object);
            $count  = count($values);
            $values = array_filter($values);
            if (!count($values)) {
                return false;
            }

            $countUnique = count(array_unique($values));
            // Validate result
            if ($this->getData('operator') == self::OPERATOR_DIFF) {
                $validatedValue = $countUnique;
            } else {
                $validatedValue = $count - $countUnique + 1; // Calculate count of similar values
            }

            // If ALL - $validatedValue should be compared with count of all products
            if ($operator == self::VALUE_OPERATOR_ALL) {
                $value = $count;
            }

            $isValid = $this->compareValues($operator, $validatedValue, $value);
        }

        return $isValid;
    }

    /**
     * Collect attribute values for each product in collection.
     *
     * @param AbstractModel $object
     *
     * @return array
     */
    private function collectValues(AbstractModel $object)
    {
        $values = [];
        $collection = $this->getCollection($object);

        foreach ($collection as $item) {
            if (parent::validate($item)) {
                $product = $item->getProduct();
                if (!$product) {
                    continue;
                }

                $attrValue = $product->getData($this->getData('attribute'));
                if (!$attrValue && $product->getTypeId() == Configurable::TYPE_CODE) {
                    $children = $item->getChildrenItems();
                    if (is_array($children) and isset($children[0])) {
                        $child = $children[0];
                        /** @var \Magento\Quote\Model\Quote\Item $child */
                        if ($child && $child->getProduct()) {
                            $attrValue = $child->getProduct()->getData($this->getData('attribute'));
                        }
                    }
                }

                $values[] = $attrValue;
            }
        }

        return $values;
    }

    /**
     * Compare validatedValue over value depending on given operator.
     *
     * @param string     $operator
     * @param int        $validatedValue
     * @param int|string $value
     *
     * @return bool
     */
    private function compareValues($operator, $validatedValue, $value)
    {
        $result = true;
        switch($operator) {
            case self::VALUE_OPERATOR_MORE:
                $result = $validatedValue > $value;
                break;
            case self::VALUE_OPERATOR_LESS:
                $result = $validatedValue < $value;
                break;
            case self::VALUE_OPERATOR_EQUAL:
                $result = $validatedValue == $value;
                break;
        }

        return $result;
    }

    /**
     * Retrieve collection associated with validated object.
     * It can be collection or quote or order items.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Sales\Model\Order\Item[]|\Magento\Quote\Model\Quote\Item[]
     */
    private function getCollection(\Magento\Framework\Model\AbstractModel $object)
    {
        $collection = [];
        if ($object->getData(QuoteData::ID)) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteFactory->create()
                ->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                ->load($object->getData(QuoteData::ID));
            $collection = $quote->getAllVisibleItems();
        } elseif ($object->getData(OrderData::ID)) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($object->getData(OrderData::ID));
            $collection = $order->getAllVisibleItems();
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray();
        $out['value_operator'] = $this->getData('value_operator');

        return $out;
    }
}