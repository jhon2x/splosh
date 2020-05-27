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


use Magento\Customer\Model\Logger;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\CustomerCondition;
use Magento\Sales\Model\ResourceModel\Sale\CollectionFactory as SaleCollectionFactory;

class LastActivityAttribute extends CustomerAbstractAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'last_activity';
    const ATTR_LABEL = 'Last activity (in days)';
    /**
     * @var Logger
     */
    private $customerLogger;

    public function __construct(
        Logger $customerLogger,
        SaleCollectionFactory $saleCollectionFactory
    ) {
        parent::__construct($saleCollectionFactory);

        $this->customerLogger = $customerLogger;
    }

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
     * Check whether customer is subscriber or not.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $log = $this->customerLogger->get($dataObject->getData(InstanceEventInterface::PARAM_CUSTOMER_ID));

        $lastActivityDate = $log->getLastVisitAt() ? $log->getLastVisitAt() : $log->getLastLoginAt();

        if ($lastActivityDate === null) {
            return 0;
        }


        return round((time() - strtotime($lastActivityDate)) / 60 / 60 / 24);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return CustomerCondition::class . '|' . self::ATTR_CODE;
    }
}
