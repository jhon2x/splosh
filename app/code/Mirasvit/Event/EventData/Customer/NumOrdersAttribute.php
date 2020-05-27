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



namespace Mirasvit\Event\EventData\Customer;


use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\CustomerCondition;
use Mirasvit\Event\EventData\CustomerData;

class NumOrdersAttribute extends CustomerAbstractAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'number_of_orders';
    const ATTR_LABEL = 'Number of Orders';
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
     * Return customer's number of orders.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $totals = $this->getCustomerTotals($dataObject);

        return $totals->getData('num_orders');
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return CustomerCondition::class . '|' . self::ATTR_CODE;
    }
}
