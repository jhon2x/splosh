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



namespace Mirasvit\Event\Plugin;

use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Repository\AttributeRepositoryInterface;

class Attributes
{
    /**
     * @var array
     */
    private $attributes;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param EventDataInterface $eventData
     * @param array              $attributes
     *
     * @return array
     */
    public function afterGetAttributes(EventDataInterface $eventData, array $attributes = [])
    {
        $attributes = array_map(function($code, $data) use ($eventData) {
            $data[AttributeInterface::CODE] = $code;

            return $this->attributeRepository->create($eventData, $data);
        }, array_keys($attributes), $attributes);

        if (is_array($eventData->getData('event_attributes'))) {
            $attributes = array_merge($attributes, $eventData->getData('event_attributes'));
        }

        // inject attributes from di.xml
        return $attributes;
    }
}
