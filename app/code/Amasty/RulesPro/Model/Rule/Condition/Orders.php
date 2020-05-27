<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Model\Rule\Condition;

use Amasty\RulesPro\Model\ResourceModel\Order;
use Magento\Rule\Model\Condition as Condition;

/**
 * Product rule condition data model
 */
class Orders extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var Order
     */
    private $orderResource;

    public function __construct(
        Condition\Context $context,
        Order $orderResource,
        array $data = []
    ) {
        $this->orderResource = $orderResource;

        parent::__construct($context, $data);
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'order_num' => __('Number of Completed Orders'),
            'sales_amount' => __('Total Sales Amount'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getValueSelectOptions()
    {
        $options = [];

        $key = 'value_select_options';
        if (!$this->hasData($key)) {
            $this->setData($key, $options);
        }

        return $this->getData($key);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        $num = 0;

        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $quote = $model->getQuote();
        }

        if ($quote->getCustomerId()) {
            $num = $this->orderResource->getValidationData($quote->getCustomerId(), $this->getAttribute());
        }

        return $this->validateAttribute($num);
    }
}
