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

class FinishedAgoAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'finished_ago';
    const ATTR_LABEL = 'Finished Ago (in minutes)';

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
     * Calculate number of minutes passed since this cron job finished time.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $model = $dataObject->getData(ScheduleData::IDENTIFIER);

        if (!$model->getData('finished_at')) {
            return 0;
        }

        return round((time() - strtotime($model->getData('finished_at'))) / 60);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return ScheduleCondition::class . '|' . self::ATTR_CODE;
    }
}
