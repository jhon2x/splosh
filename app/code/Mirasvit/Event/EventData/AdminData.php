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

use Magento\User\Model\User;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\AdminCondition;

class AdminData extends User implements EventDataInterface
{
    const ID = 'user_id';
    const IDENTIFIER = 'admin';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return AdminCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Admin');
    }

    /**v
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'firstname'     => [
                'label' => __('First Name'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'lastname'      => [
                'label' => __('Last Name'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'username'   => [
                'label' => __('Username'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'is_active'   => [
                'label' => __('Is Active'),
                'type'  => self::ATTRIBUTE_TYPE_BOOL,
            ],
            'lognum'   => [
                'label' => __('Number of logins'),
                'type'  => self::ATTRIBUTE_TYPE_NUMBER,
            ],
            'logdate' => [
                'label' => __('Last login date'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ],
            'failures_num'   => [
                'label' => __('Number of failed logins'),
                'type'  => self::ATTRIBUTE_TYPE_NUMBER,
            ],
            'updated_at'  => [
                'label' => __('Updated At'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ],
            'created_at'  => [
                'label' => __('Created At'),
                'type'  => self::ATTRIBUTE_TYPE_DATE,
            ],
        ];
    }
}