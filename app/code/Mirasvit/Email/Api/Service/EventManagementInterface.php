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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Api\Service;

use Mirasvit\Event\Model\ResourceModel\Event\Collection;

interface EventManagementInterface
{
    /**
     * Filters events by new, that are not processed with triggers yet.
     *
     * @param Collection $collection
     * @param int        $triggerId
     * @param int|array  $storeIds
     *
     * @return Collection
     */
    public function addNewFilter(Collection $collection, $triggerId, $storeIds);

    /**
     * Add processed trigger id.
     *
     * @param int $eventId
     * @param int $triggerId
     *
     * @return void
     */
    public function addProcessedTriggerId($eventId, $triggerId);

    /**
     * Remove processed triggers.
     *
     * @param int $eventId
     *
     * @return bool
     */
    public function removeProcessedTriggers($eventId);

    /**
     * Get processed trigger ids.
     *
     * @param int $eventId
     *
     * @return array
     */
    public function getProcessedTriggerIds($eventId);
}
