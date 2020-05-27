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


use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;

class Attribute extends DataObject implements AttributeInterface
{
    /**
     * @var EventDataInterface
     */
    private $eventData;

    public function __construct(
        EventDataInterface $eventData,
        array $data = []
    ) {
        parent::__construct($data);

        $this->eventData = $eventData;
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return __($this->getData(self::LABEL));
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * At first, the value is checked in the $dataObject itself,
     * but if it's empty the value is used from the Entity Model.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $model = $dataObject->getData($this->eventData->getIdentifier());

        // use value from Event Params if it exists there, otherwise use value from Entity Model
        $value = ($data = $this->getObjectData($dataObject, $this->getCode())) !== null
            ? $data
            // if model is not of type DataObject return false
            : ($model instanceof \Magento\Framework\DataObject
                ? $this->getObjectData($model, $this->getCode())
                : false
            );

        return $this->getType() == EventDataInterface::ATTRIBUTE_TYPE_BOOL ? (int) $value : $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return $this->eventData->getConditionClass() . '|' . $this->getCode();
    }

    /**
     * Get object value by given $path.
     *
     * @param DataObject $object - host object
     * @param string     $path   - path to the value in host object, e.g. 'ticket.code'
     * @param int        $index  - index of used key from path
     *
     * @return mixed|null
     */
    private function getObjectData(DataObject $object, $path, $index = 0)
    {
        $keys = explode('.', $path);
        //$result = $object->getDataUsingMethod($keys[$index++]);
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $keys[$index++])));
        $result = $object->{$method}();

        if (count($keys) === $index) {
            return $result;
        } elseif (!$result instanceof DataObject) {
            return null;
        }

        return $this->getObjectData($result, $path, $index);
    }
}
