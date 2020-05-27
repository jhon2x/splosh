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



namespace Mirasvit\Event\EventData\Schedule;


use Magento\Cron\Model\Schedule;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ScheduleCondition;
use Mirasvit\Event\EventData\ScheduleData;

class LastHeartbeatAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'last_heartbeat';
    const ATTR_LABEL = 'Last heartbeat time (in minutes)';

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return self::ATTR_CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return __(self::ATTR_LABEL);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return EventDataInterface::ATTRIBUTE_TYPE_NUMBER;
    }

    /**
     * Calculate last heartbeat time (in minutes).
     *
     * Last Heartbeat time = current time - executed_at of last cron job.
     * If no cron jobs found - return 0.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        /** @var Schedule $schedule */
        $schedule = $dataObject->getData(ScheduleData::IDENTIFIER);
        $schedules = $schedule->getCollection()->addFieldToFilter('status', Schedule::STATUS_SUCCESS);
        $schedules->getSelect()->limit(1)->order('executed_at DESC');
        $schedules->load();

        if (!$schedules->getSize()) {
            return 0;
        }

        $executedAt = $schedules->getFirstItem()->getData('executed_at');

        return round((time() - strtotime($executedAt)) / 60);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return ScheduleCondition::class . '|' . self::ATTR_CODE;
    }
}
