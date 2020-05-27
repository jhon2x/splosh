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


namespace Mirasvit\Event\Service\Config;

use Mirasvit\Event\Api\Repository\EventRepositoryInterface;
use Mirasvit\Event\Model\Rule\ConditionFactory;
use Mirasvit\Event\Service\Config\Map\Converter;

class Map
{
    /**
     * @var Map\Data
     */
    private $data;

    /**
     * Map constructor.
     *
     * @param Map\Data $data
     */
    public function __construct(Map\Data $data)
    {
        $this->data = $data;
    }

    /**
     * Load virtual events to Event Repository.
     *
     * @param EventRepositoryInterface $eventRepository
     *
     * @return $this
     */
    public function loadEvents(EventRepositoryInterface $eventRepository)
    {
        $events = $this->data->get('config/events/event');
        if ($events && is_array($events)) {
            foreach ($events as $data) {
                $eventRepository->addEvent($data[Converter::DATA_ATTRIBUTES_KEY][Converter::DATA_NAME_KEY]);
            }
        }

        return $this;
    }

    /**
     * Load virtual conditions to Condition Factory.
     *
     * @param ConditionFactory $conditionFactory
     *
     * @return $this
     */
    public function loadConditions(ConditionFactory $conditionFactory)
    {
        $conditions = $this->data->get('config/conditions/condition');
        if ($conditions && is_array($conditions)) {
            foreach ($conditions as $data) {
                $conditionFactory->addCondition($data[Converter::DATA_ATTRIBUTES_KEY][Converter::DATA_NAME_KEY]);
            }
        }

        return $this;
    }
}
