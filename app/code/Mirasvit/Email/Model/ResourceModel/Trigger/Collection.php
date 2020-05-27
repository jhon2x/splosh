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



namespace Mirasvit\Email\Model\ResourceModel\Trigger;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = TriggerInterface::ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\Email\Model\Trigger::class, \Mirasvit\Email\Model\ResourceModel\Trigger::class);
    }

    /**
     * Add active filter
     *
     * @return $this
     */
    public function addActiveFilter()
    {
        $date = new \DateTime();

        $activeFrom = [];
        $activeFrom[] = ['date' => true, 'to' => $date->format('Y-m-d H:i:s')];
        $activeFrom[] = ['date' => true, 'eq' => '0000-00-00 00:00:00'];
        $activeFrom[] = ['date' => true, 'null' => true];

        $activeTo = [];
        $activeTo[] = ['date' => true, 'from' => $date->format('Y-m-d H:i:s')];
        $activeTo[] = ['date' => true, 'eq' => '0000-00-00 00:00:00'];
        $activeTo[] = ['date' => true, 'null' => true];

        $this->addFieldToFilter('main_table.'.TriggerInterface::IS_ACTIVE, TriggerInterface::STATUS_ACTIVE);
        $this->addFieldToFilter(TriggerInterface::ACTIVE_FROM, $activeFrom);
        $this->addFieldToFilter(TriggerInterface::ACTIVE_TO, $activeTo);

        // filter by active campaigns
        $this->getSelect()->order('main_table.'.CampaignInterface::ID . ' ' . self::SORT_ORDER_ASC);
        $this->addFieldToFilter('campaign.'.CampaignInterface::IS_ACTIVE, CampaignInterface::STATUS_ACTIVE);
        $this->getSelect()->joinLeft(
            ['campaign' => $this->getTable(CampaignInterface::TABLE_NAME)],
            'main_table.campaign_id = campaign.'.CampaignInterface::ID,
            []
        );

        return $this;
    }

    /**
     * Add event filter
     *
     * @param string $value
     * @return $this
     */
    public function addEventFilter($value)
    {
        $this->addFieldToFilter(TriggerInterface::EVENT, $value);

        return $this;
    }

    /**
     * Add filter for event, cancelcation event
     *
     * @param string $value
     * @return $this
     * @todo RF
     */
    public function addEventOrFilter($value)
    {
        $this->getSelect()->where(
            'find_in_set(?, '
            . TriggerInterface::CANCELLATION_EVENT . ') OR '
            . TriggerInterface::EVENT . '=?',
            $value,
            $value
        );

        return $this;
    }

    /**
     * Add cancelation event filter
     *
     * @param string $value
     * @return $this
     */
    public function addCancellationEventFilter($value)
    {
        $this->getSelect()->where('find_in_set(?, ' . TriggerInterface::CANCELLATION_EVENT . ')', $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash(TriggerInterface::ID, TriggerInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray(TriggerInterface::ID, TriggerInterface::TITLE);
    }
}
