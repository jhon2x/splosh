<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_RulesPro
 */


namespace Amasty\RulesPro\Model\Rule\Condition;

use Magento\Rule\Model\Condition as Condition;

/**
 * Customer rule condition data model
 */
class Customer extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var array
     */
    protected $notAllowedAttributes = [
        'lock_expires',
        'first_failure',
        'group_id',
        'default_billing',
        'default_shipping',
        'failures_num',
        'created_in',
        'disable_auto_group_change',
        'confirmation'
    ];

    public function __construct(
        Condition\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|false
     */
    public function getAttributeObject()
    {
        return $this->resource->getAttribute($this->getAttribute());
    }

    /**
     * @return $this|Condition\AbstractCondition
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->resource
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($customerAttributes as $attribute) {
            if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), $this->notAllowedAttributes)) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['id'] = __('Customer ID');
        $attributes['membership_days'] = __('Membership Days');
    }

    /**
     * @return Condition\AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() == 'entity_id' || $this->getAttribute() == 'membership_days') {
            return 'string';
        }
        $customerAttribute = $this->getAttributeObject();
        if (!$customerAttribute) {
            return parent::getInputType();
        }

        return $this->getInputTypeFromAttribute($customerAttribute);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerAttribute
     *
     * @return string
     */
    protected function getInputTypeFromAttribute($customerAttribute)
    {
        if (!is_object($customerAttribute)) {
            $customerAttribute = $this->getAttributeObject();
        }
        $possibleTypes = ['string', 'numeric', 'date', 'select', 'multiselect', 'grid', 'boolean'];
        if (in_array($customerAttribute->getFrontendInput(), $possibleTypes)) {
            return $customerAttribute->getFrontendInput();
        }
        switch ($customerAttribute->getFrontendInput()) {
            case 'gallery':
            case 'media_image':
            case 'selectimg': // amasty customer attribute
                return 'select';
            case 'multiselectimg': // amasty customer attribute
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * @return Condition\AbstractCondition
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getInputType()) {
            case 'date':
                $element->setClass('hasDatepicker');
                break;
        }

        return $element;
    }

    /**
     * @return bool
     */
    public function getExplicitApply()
    {
        return ($this->getInputType() == 'date');
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see \Magento\Framework\Data\Form\Element
     * @return string
     */
    public function getValueElementType()
    {
        $customerAttribute = $this->getAttributeObject();

        if ($this->getAttribute() === 'entity_id' || $this->getAttribute() == 'membership_days') {
            return 'text';
        }
        if (!is_object($customerAttribute)) {
            return parent::getValueElementType();
        }

        $availableTypes = [
            'checkbox',
            'checkboxes',
            'date',
            'editablemultiselect',
            'editor',
            'fieldset',
            'file',
            'gallery',
            'image',
            'imagefile',
            'multiline',
            'multiselect',
            'radio',
            'radios',
            'select',
            'text',
            'textarea',
            'time'
        ];

        if (in_array($customerAttribute->getFrontendInput(), $availableTypes)) {
            return $customerAttribute->getFrontendInput();
        }
        switch ($customerAttribute->getFrontendInput()) {
            case 'selectimg':
            case 'boolean':
                return 'select';
            case 'multiselectimg':
                return 'multiselect';
        }

        return parent::getValueElementType();
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $selectOptions = [];
        $attributeObject = $this->getAttributeObject();

        if (is_object($attributeObject) && $attributeObject->usesSource()) {
            $addEmptyOption = true;
            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
        }

        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
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
        $customer = $model;
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            $customer = $model->getQuote()->getCustomer();

            if (!$customer->getId()) {
                $customer = $this->customerSession->getCustomer();
            }

            $attr     = $this->getAttribute();
            $allAttr = $customer instanceof \Magento\Customer\Model\Customer
                ? $customer->getData() : $customer->__toArray();

            if ($attr == 'membership_days') {
                $allAttr[$attr] = $this->getMembership($customer->getCreatedAt());
            }

            if ($attr != 'entity_id' && !array_key_exists($attr, $allAttr)) {
                if (isset($allAttr['custom_attributes']) && array_key_exists($attr, $allAttr['custom_attributes'])) {
                    $customAttribute = $this->resource->getAttribute($attr);
                    $attributeValue = $allAttr['custom_attributes'][$attr]['value'];

                    if ($customAttribute->getFrontendInput() == 'multiselect') {
                        $attributeValue = explode(',', $attributeValue);
                    }

                    $allAttr[$attr] = $attributeValue;
                } else {
                    $address        = $model->getQuote()->getBillingAddress();
                    $allAttr[$attr] = $address->getData($attr);
                }
            }

            $customer = $this->customerFactory->create()->setData($allAttr);
        }

        return parent::validate($customer);
    }

    /**
     * @param string $created
     *
     * @return float
     */
    public function getMembership($created)
    {
        return round((time() - strtotime($created)) / 60 / 60 / 24);
    }
}
