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

use Magento\Store\Model\Store;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Service\OptionsConverterInterface;
use Mirasvit\Event\EventData\Condition\StoreCondition;

class StoreData extends Store implements EventDataInterface
{
    use ContextTrait;

    const ID = self::STORE_ID;

    const IDENTIFIER = 'store';

    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    public function getConditionClass()
    {
        return StoreCondition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Store');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $attributes = [
            'base_url'     => [
                'label' => __('Base URL'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
            'frontend_name'      => [
                'label' => __('Store Name'),
                'type'  => self::ATTRIBUTE_TYPE_STRING,
            ],
        ];

        /** @var OptionsConverterInterface $converter */
        $converter = $this->get(OptionsConverterInterface::class);

        $stores = $converter->convert($this->get('\Magento\Store\Model\System\Store')->toOptionArray());

        // add store condition for multi-stores only
        if (count($stores) > 1) {
            $attributes[self::ID] = [
                'label'   => __('Store View'),
                'type'    => self::ATTRIBUTE_TYPE_ENUM,
                'options' => $stores,
            ];
        }

        return $attributes;
    }
}