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

use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ScheduleCondition;
use Magento\Cron\Model\Schedule;

class ScheduleData extends Schedule implements EventDataInterface
{
    const ID = 'schedule_id';
    const IDENTIFIER = 'schedule';

    public function getLabel()
    {
        return __('Schedule');
    }

    public function getConditionClass()
    {
        return ScheduleCondition::class;
    }

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getAttributes()
    {
        return [
            'job_code'     => [
                'label' => __('Job Code'),
                'type'  => 'string',
            ],
            'status'       => [
                'label'   => __('Status'),
                'type'    => 'enum',
                'options' => [
                    Schedule::STATUS_PENDING => __('Pending'),
                    Schedule::STATUS_RUNNING => __('Running'),
                    Schedule::STATUS_ERROR   => __('Error'),
                    Schedule::STATUS_MISSED  => __('Missed'),
                    Schedule::STATUS_SUCCESS => __('Success'),
                ],
            ],
            'message'      => [
                'label' => __('Message'),
                'type'  => 'string',
            ],
            'created_at'   => [
                'label' => __('Created At'),
                'type'  => 'date',
            ],
            'scheduled_at' => [
                'label' => __('Scheduled At'),
                'type'  => 'date',
            ],
            'executed_at'  => [
                'label' => __('Executed At'),
                'type'  => 'date',
            ],
            'finished_at'  => [
                'label' => __('Finished At'),
                'type'  => 'date',
            ],
        ];
    }
}