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



namespace Mirasvit\Event\Event;


use Magento\Framework\DataObject;
use Mirasvit\Event\Api\Data\Event\ObservableEventWrapperInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;

class ObservableEventWrapper extends ObservableEvent implements ObservableEventWrapperInterface
{
    /**
     * @var EventDataInterface
     */
    private $eventData;
    /**
     * @var Context
     */
    private $observedClassName;
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $stringFormat;
    /**
     * @var array
     */
    private $keys;
    /**
     * @var array
     */
    private $conditions;
    /**
     * @var string
     */
    private $primaryId;
    /**
     * @var int
     */
    private $expireAfter;

    /**
     * ObservableEventWrapper constructor.
     *
     * @param Context            $context
     * @param EventDataInterface $eventData         - event data class used for event
     * @param Context            $observedClassName - model's class name to be observed
     * @param string             $primaryId         - model's table primary ID field name
     * @param string             $identifier        - event identifier
     * @param string             $label             - event label
     * @param string             $stringFormat      - format for the event toString method: '%1: %2'. Keys used from the $keys argument.
     * @param array              $keys              - model keys that should be registered as params for event: [message_id, message, date]
     * @param array              $conditions        - conditions that should be satisfied to register an event: [key => value]
     * @param int                $expireAfter       - expire after time of event
     */
    public function __construct(
        Context $context,
        EventDataInterface $eventData,
        $observedClassName,
        $primaryId,
        $identifier,
        $label,
        $stringFormat,
        array $keys,
        array $conditions = [],
        $expireAfter = 3600
    ) {
        parent::__construct($context);

        $this->eventData = $eventData;
        $this->observedClassName = $observedClassName;
        $this->identifier = $identifier;
        $this->label = $label;
        $this->keys = $keys;
        $this->conditions = $conditions;
        $this->primaryId = $primaryId;
        $this->expireAfter = $expireAfter;
        $this->stringFormat = $this->prepareStringFormat($stringFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            $this->identifier => $this->label
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->eventData
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $values = [];
        $params = $this->expand($params);

        // prepare values for '__' method
        foreach ($this->stringFormat['keys'] as $key) {
            if (isset($params[$key])) {
                $values[] = $params[$key];
            } else {
                $values[] = $this->getObjectData($params[$this->eventData->getIdentifier()], $key);
            }
        }

        return __($this->stringFormat['format'], ...$values);
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $model = $this->context->create($this->observedClassName)->load($params[$this->primaryId]);

        $params[$this->eventData->getIdentifier()] = $model;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getObservedClassName()
    {
        return $this->observedClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function register(DataObject $object)
    {
        if ($this->canRegister($object)) {
            $params = [];

            $params[$this->primaryId] = $object->getData($this->primaryId);
            $params[self::PARAM_EXPIRE_AFTER] = $this->expireAfter;
            foreach ($this->keys as $path) {
                $params[$this->getKey($path)] = $this->getObjectData($object, $path);
            }

            $this->context->eventRepository->register(
                $this->identifier,
                [$params[$this->primaryId]],
                $params
            );
        }

        return false;
    }

    /**
     * Validate object over event condition.
     *
     * @param DataObject $object
     *
     * @return bool
     */
    private function canRegister(DataObject $object)
    {
        foreach ($this->conditions as $path => $value) {
            if ($this->getObjectData($object, $path) !== $value) {
                return false;
            }
        }

        return true;
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
    protected function getObjectData(DataObject $object, $path, $index = 0)
    {
        $keys = explode('.', $path);
        $result = $object->getDataUsingMethod($keys[$index++]);

        if (count($keys) === $index) {
            return $result;
        } elseif (!$result instanceof DataObject) {
            return null;
        }

        return $this->getObjectData($result, $path, $index);
    }

    /**
     * Returns last key from the given path.
     *
     * @param string $path - e.g. 'ticket.code'
     *
     * @return string|bool - 'ticket.code' => 'code'
     */
    private function getKey($path)
    {
        $keys = explode('.', $path);

        return end($keys);
    }

    /**
     * Prepare string format for use.
     *
     * @param array $stringFormat
     *
     * @return array
     */
    private function prepareStringFormat(array $stringFormat)
    {
        $stringFormat['format'] = str_replace('\n', "\n", $stringFormat['format']);

        return $stringFormat;
    }
}
