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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Model\ResourceModel;

use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

trait CollectionTrait
{
    public function joinQueue()
    {
        $this->getSelect()->join(
            ['queue' => $this->_resource->getTable(QueueInterface::TABLE_NAME)],
            'queue.' . QueueInterface::ID . ' = main_table.queue_id',
            []
        )->join(
            ['trigger' => $this->_resource->getTable(TriggerInterface::TABLE_NAME)],
            'trigger.' . TriggerInterface::ID . ' = queue.trigger_id',
            []
        );

        return $this;
    }

    public function aggregate($field, $aggregator = 'COUNT')
    {
        $this->getSelect()->columns(['value' => new \Zend_Db_Expr("$aggregator($field)")]);

        return floatval($this->getFirstItem()->getData('value'));
    }

    public function addFieldToFilter($attribute, $condition = null)
    {
        if ($attribute == CampaignInterface::ID) {
            $attribute = 'trigger.' . $attribute;
        } elseif ($attribute == TriggerInterface::ID) {
            $attribute = 'queue.' . $attribute;
        } elseif ($attribute == ChainInterface::ID) {
            $attribute = 'queue.' . $attribute;
        }

        return parent::addFieldToFilter($attribute, $condition);
    }
}
