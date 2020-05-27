<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Model\Rule\Condition;

/**
 * Total Condition(s).
 */
class Total extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var array
     */
    private $passedItems = [];

    /**
     * @var \Amasty\RulesPro\Helper\Calculator
     */
    private $calculator;

    public function __construct(
        \Amasty\RulesPro\Helper\Calculator $calculator,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        $this->calculator = $calculator;

        parent::__construct($context, $data); //DO NOT TOUCH POSITION OF PARENT CALL !!!

        $this->setType(Total::class)->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     *
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);

        return $this;
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     *
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );

        return $xml;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'average_order_value' => __('Average Order Value'),
                'total_orders_amount' => __('Total Sales Amount'),
                'of_placed_orders' => __('Number of Placed Orders'),
            ]
        );

        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );

        return $this;
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [
            [
                'label' => __('Please choose condition'),
                'value' => ''
            ],
            [
                'label' => __('Order State'),
                'value' => \Amasty\RulesPro\Model\Rule\Condition\Total\Status::class
            ],
            [
                'label' => __('Period after order was placed'),
                'value' => \Amasty\RulesPro\Model\Rule\Condition\Total\Period::class
            ],
        ];

        return $conditions;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
                $this->getAttributeElement()->getHtml(),
                $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $quote = $model->getQuote();
        }

        // order history conditions are valid for customers only, not for visitors.
        $customerId = $quote->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $condArray = [];

        foreach ($this->getConditions() as $condObj) {
            if (!in_array($condObj->getId(), $this->passedItems)) {
                $this->passedItems[] = $condObj->getId();
                $condArray[] = $condObj->validate($model);
            }
        }

        if (empty($condArray)) {
            return $this->validateAttribute($model->getData($this->getAttribute()));
        }

        $fieldName = $this->getAttributeElement()->getValue();

        $attribute
            = $this->calculator->getSingleTotalField($customerId, $fieldName, $condArray, $this->getAggregator());

        return $this->validateAttribute($attribute);
    }
}
