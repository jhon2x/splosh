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



namespace Mirasvit\Event\Api\Repository;


use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;

interface AttributeRepositoryInterface
{
    /**
     * Get attribute instance from event data by code.
     *
     * @param int                $code
     * @param EventDataInterface $eventData
     *
     * @return AttributeInterface
     */
    public function get($code, EventDataInterface $eventData);

    /**
     * Create attribute instance from event data with initialized data.
     *
     * @param EventDataInterface $eventData
     * @param array              $data
     *
     * @return AttributeInterface
     */
    public function create(EventDataInterface $eventData, array $data = []);
}
