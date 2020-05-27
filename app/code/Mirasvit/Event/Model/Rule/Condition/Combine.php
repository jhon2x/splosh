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



namespace Mirasvit\Event\Model\Rule\Condition;

use Magento\Framework\ObjectManagerInterface;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Repository\AttributeRepositoryInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var string
     */
    private $eventIdentifier;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        Context $context,
        EventRepositoryInterface $eventRepository,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->eventRepository = $eventRepository;
        if (isset($data['eventIdentifier'])) {
            $this->eventIdentifier = $data['eventIdentifier'];
        }
        $this->objectManager = $objectManager;

        parent::__construct($context, $data);

        $this->setType(get_class($this));
    }

    public function setEventIdentifier($identifier)
    {
        $this->eventIdentifier = $identifier;

        return true;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => self::class,
                'label' => __('Conditions Combination'),
            ],
        ]);

        $eventInstance = $this->eventRepository->getInstance($this->eventIdentifier);

        if ($eventInstance) {
            foreach ($eventInstance->getEventData() as $type) {
                $attributes = [];

                $condition = $this->objectManager->create($type->getConditionClass());

                // we can add condition group to any EventData via di.xml
                $conditions = array_merge_recursive($conditions, (array)$type->getData('condition_group'));

                foreach ($condition->loadAttributeOptions()->getAttributeOption() as $code => $label) {
                    $attribute = $this->attributeRepository->get($code, $type);

                    $attributes[] = [
                        'value' => $attribute->getConditionClass(),
                        'label' => $label,
                    ];
                }

                $conditions[] = [
                    'label' => $type->getLabel(),
                    'value' => $attributes,
                ];
            }
        }

        return $conditions;
    }
}
