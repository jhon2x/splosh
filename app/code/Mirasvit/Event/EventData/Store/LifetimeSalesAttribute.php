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



namespace Mirasvit\Event\EventData\Store;


use Magento\Store\Model\Store;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\StoreCondition;
use Mirasvit\Event\EventData\StoreData;

class LifetimeSalesAttribute extends StoreAbstractAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'lifetime';
    const ATTR_LABEL = 'Lifetime Sales';
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
     * Return store lifetime sales.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        /** @var Store $model */
        $model = $dataObject->getData(StoreData::IDENTIFIER);

        $totals = $this->getStoreTotals($model);

        return $totals->getData('lifetime');
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return StoreCondition::class . '|' . self::ATTR_CODE;
    }
}
