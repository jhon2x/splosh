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



namespace Mirasvit\Event\EventData\Condition;

use Magento\Framework\App\ObjectManager;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Model\Event;
use Mirasvit\Event\Api\Repository\AttributeRepositoryInterface;

/**
 * @method string getAttribute()
 */
abstract class AbstractCondition extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        Context $context,
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->objectManager = ObjectManager::getInstance();

        parent::__construct($context, $data);
    }

    /**
     * @return EventDataInterface
     */
    public function getEventData()
    {
        return $this->objectManager->get($this->getEventDataClass());
    }

    public abstract function getEventDataClass();

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [];

        foreach ($this->getEventData()->getAttributes() as $attr) {
            $attributes[$attr->getCode()] = $this->getEventData()->getLabel() . ': ' . $attr->getLabel();
        }

        $this->setAttributeOption($attributes);

        return $this;
    }


    public function getValueOption($option = null)
    {

        foreach ($this->getEventData()->getAttributes() as $attribute) {
            if ($this->getAttribute() == $attribute->getCode()) {
                if (in_array($attribute->getType(),[
                    EventDataInterface::ATTRIBUTE_TYPE_ENUM,
                    EventDataInterface::ATTRIBUTE_TYPE_ENUM_MULTI
                ])) {
                    $this->setData('value_option', $attribute->getOptions());
                } elseif ($attribute->getType() == EventDataInterface::ATTRIBUTE_TYPE_BOOL) {
                    $yesNo = $this->objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toArray();
                    $this->setData('value_option', $yesNo);
                }
            }
        }

        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    public function getInputType()
    {
        //'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
        //'numeric' => ['==', '!=', '>=', '>', '<=', '<', '()', '!()'],
        //'date' => ['==', '>=', '<='],
        //'select' => ['==', '!='],
        //'boolean' => ['==', '!='],
        //'multiselect' => ['{}', '!{}', '()', '!()'],
        //'grid' => ['()', '!()'],


        foreach ($this->getEventData()->getAttributes() as $attribute) {
            if ($this->getAttribute() == $attribute->getCode()) {
                switch ($attribute->getType()) {
                    case EventDataInterface::ATTRIBUTE_TYPE_STRING:
                        return 'string';
                    case EventDataInterface::ATTRIBUTE_TYPE_NUMBER:
                        return 'numeric';
                    case EventDataInterface::ATTRIBUTE_TYPE_ENUM:
                        return 'select';
                    case EventDataInterface::ATTRIBUTE_TYPE_ENUM_MULTI:
                        return 'multiselect';
                    case EventDataInterface::ATTRIBUTE_TYPE_DATE:
                        return 'date';
                    case EventDataInterface::ATTRIBUTE_TYPE_BOOL:
                        return 'boolean';
                }
            }
        }

        return 'string';
    }

    public function getValueElementType()
    {
        foreach ($this->getEventData()->getAttributes() as $attribute) {
            if ($this->getAttribute() == $attribute->getCode()) {
                switch ($attribute->getType()) {
                    case EventDataInterface::ATTRIBUTE_TYPE_STRING:
                        return 'text';
                    case EventDataInterface::ATTRIBUTE_TYPE_NUMBER:
                        return 'text';
                    case EventDataInterface::ATTRIBUTE_TYPE_ENUM_MULTI:
                        return 'multiselect';
                    case EventDataInterface::ATTRIBUTE_TYPE_ENUM:
                    case EventDataInterface::ATTRIBUTE_TYPE_BOOL:
                        return 'select';
                    case EventDataInterface::ATTRIBUTE_TYPE_DATE:
                        return 'date';
                }
            }
        }

        return 'text';
    }

    public function getDefaultOperatorInputByType()
    {
        return [
            'string'      => ['==', '!=', '{}', '!{}'],
            'numeric'     => ['==', '!=', '>=', '>', '<=', '<'],
            'date'        => ['==', '>=', '<='],
            'select'      => ['==', '!='],
            'boolean'     => ['==', '!='],
            'multiselect' => ['{}', '!{}', '()', '!()'],
            'grid'        => ['()', '!()'],
        ];
    }

    /**
     * Default operator options getter
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        return [
            '=='  => __('is'),
            '!='  => __('is not'),
            '>='  => __('equals or greater than'),
            '<='  => __('equals or less than'),
            '>'   => __('greater than'),
            '<'   => __('less than'),
            '{}'  => __('contains'),
            '!{}' => __('does not contain'),
            '()'  => __('is one of'),
            '!()' => __('is not one of'),
        ];
    }

    public function validate(AbstractModel $dataObject)
    {
        $attribute = $this->attributeRepository->get($this->getAttribute(), $this->getEventData());

        $attributeValue = $attribute->getValue($dataObject);

        return $this->validateAttribute($attributeValue);
    }

    public function getJsFormObject()
    {
        return 'conditions_fieldset';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setData('show_as_text', true); // Do not allow to choose other elements from current optgroup

        return $element;
    }
}