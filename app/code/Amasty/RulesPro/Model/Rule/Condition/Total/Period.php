<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */

namespace Amasty\RulesPro\Model\Rule\Condition\Total;

/**
 * Product rule condition data model
 */
class Period extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'period' => __('Period after order was placed'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '>=' => __('equals or less than'),
                '<=' => __('equals or greater than'),
                '>' => __('less than'),
                '<' => __('greater than'),
                '=' => __('is'),
            ]
        );

        return $this;
    }

    /**
     * @return \Magento\Rule\Model\Condition\AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return array|mixed
     */
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
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return array
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $v = min(16000, $this->getValue()); // on windows can work incorrect for very big values.

        $date = date("Y-m-d H:i:s", time() - $v * 24 * 3600);
        $result = ['date' => $this->getOperatorForValidate() . "'" . $date . "'"];

        return $result;
    }
}
