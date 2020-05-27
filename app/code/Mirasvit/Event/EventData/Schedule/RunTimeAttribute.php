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


use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\ScheduleCondition;
use Mirasvit\Event\EventData\ScheduleData;

class RunTimeAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'run_time';
    const ATTR_LABEL = 'Run Time (in minutes)';

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
     * Calculate run time for cron job in minutes.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $model = $dataObject->getData(ScheduleData::IDENTIFIER);
        // if job has not been started yet return 0
        if (!$model->getData('executed_at')) {
            return 0;
        }

        // subtract from finished_at date or current time if job has not been finished yet
        $finishedAt = $model->getData('finished_at') ? strtotime($model->getData('finished_at')) : time();

        return round(($finishedAt - strtotime($model->getData('executed_at'))) / 60);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return ScheduleCondition::class . '|' . self::ATTR_CODE;
    }
}
