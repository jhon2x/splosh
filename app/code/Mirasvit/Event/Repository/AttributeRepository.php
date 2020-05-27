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



namespace Mirasvit\Event\Repository;


use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Data\AttributeInterfaceFactory;
use Mirasvit\Event\Api\Repository\AttributeRepositoryInterface;

class AttributeRepository implements AttributeRepositoryInterface
{
    private $attributeRegistry = [];

    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeFactory;

    public function __construct(AttributeInterfaceFactory $attributeFactory)
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($code, EventDataInterface $eventData)
    {
        if (isset($this->attributeRegistry[$eventData->getIdentifier()][$code])) {
            return $this->attributeRegistry[$eventData->getIdentifier()][$code];
        }

        $attribute = null;
        $attributes = $eventData->getAttributes();

        foreach ($attributes as $attr) {
            if ($attr->getCode() == $code) {
                $attribute = $attr;
                break;
            }
        }

        if ($attribute === null) {
            return false;
        }

        if (is_array($attribute)) {
            $attribute = $this->create($eventData, $attributes[$code]);
        }

        $this->attributeRegistry[$eventData->getIdentifier()][$code] = $attribute;

        return $attribute;
    }

    /**
     * {@inheritDoc}
     */
    public function create(EventDataInterface $eventData, array $data = [])
    {
        return $this->attributeFactory->create(['eventData' => $eventData, 'data' => $data]);
    }
}
