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

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Service\OptionsConverterInterface;
use Mirasvit\Event\EventData\Condition\CustomerCondition;

class CustomerData extends Customer implements EventDataInterface
{
    use ContextTrait;

    const ID = 'customer_id';
    const IDENTIFIER = 'customer';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return CustomerCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Customer');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        /** @var OptionsConverterInterface $converter */
        $converter = $this->get(OptionsConverterInterface::class);

        $attributes = [];
        $customerAttributes = parent::getAttributes();

        // Retrieve all customer attributes
        foreach ($customerAttributes as $attr) {
            if (!$attr->getStoreLabel() || !$attr->getAttributeCode()) {
                continue;
            }

            $attributes[$attr->getAttributeCode()] = [
                'label' => $attr->getStoreLabel(),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ];
        }

        // Additional attributes
        $attributes[CustomerInterface::GROUP_ID] = [
            'label'   => __('Group'),
            'type'    => self::ATTRIBUTE_TYPE_ENUM_MULTI,
            'options' => $converter->convert(
                $this->get('Magento\Customer\Model\Config\Source\Group\Multiselect')->toOptionArray()
            )
        ];

        return $attributes;
    }
}