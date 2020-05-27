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



namespace Mirasvit\Event\EventData\Admin;


use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Event\Admin\LoginEvent;
use Mirasvit\Event\EventData\AdminData;
use Mirasvit\Event\EventData\Condition\AdminCondition;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory as UserSessionCollectionFactory;

class IsNewIpAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'is_new_ip';
    const ATTR_LABEL = 'Is Admin Logged In From a New IP';

    /**
     * @var UserSessionCollectionFactory
     */
    private $userSessionCollectionFactory;

    public function __construct(UserSessionCollectionFactory $userSessionCollectionFactory)
    {
        $this->userSessionCollectionFactory = $userSessionCollectionFactory;
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
        return EventDataInterface::ATTRIBUTE_TYPE_BOOL;
    }

    /**
     * Determined whether admin logged in from a new IP or not.
     *
     * If earlier a user's IP has been saved in "admin_user_session" table consider it as old,
     * otherwise - as a new IP.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        /** @var \Magento\User\Model\User $model */
        $model = $dataObject->getData(AdminData::IDENTIFIER);

        /** @var Collection $collection */
        $collection = $this->userSessionCollectionFactory->create();
        $collection->addFieldToFilter('user_id', $model->getId())
            //->addFieldToFilter('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
            ->addFieldToFilter('ip', $dataObject->getData(LoginEvent::PARAM_IP));

        // for type boolean return 1 or 0
        return (int) empty($collection->getSize());
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return AdminCondition::class . '|' . self::ATTR_CODE;
    }
}
