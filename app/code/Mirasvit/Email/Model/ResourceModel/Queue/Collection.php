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



namespace Mirasvit\Email\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Model\Queue;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = QueueInterface::ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Email\Model\Queue', 'Mirasvit\Email\Model\ResourceModel\Queue');
    }

    /**
     * Add ready filter
     *
     * @return $this
     */
    public function addReadyFilter()
    {
        $this->addFieldToFilter(
            'scheduled_at',
            ['lt' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)]
        )->addFieldToFilter('sent_at', ['null' => true])
            ->addFieldToFilter('status', QueueInterface::STATUS_PENDING);

        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter('status', QueueInterface::STATUS_PENDING);

        return $this;
    }

    /**
     * @return $this
     */
    public function addSentFilter()
    {
        $this->addFieldToFilter('status', QueueInterface::STATUS_SENT);

        return $this;
    }

    /**
     * @return $this
     */
    public function addCanceledFilter()
    {
        $this->addFieldToFilter('status', QueueInterface::STATUS_CANCELED);

        return $this;
    }

    /**
     * @param \Mirasvit\Email\Model\Trigger $trigger
     * @return $this
     */
    public function addTriggerFilter($trigger)
    {
        $this->addFieldToFilter('trigger_id', $trigger->getId());

        return $this;
    }
}
