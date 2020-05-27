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



namespace Mirasvit\Email\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Helper\Serializer;

class Queue extends AbstractDb
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param Serialize $serializer
     * @param Context   $context
     */
    public function __construct(
        Serializer $serializer,
        Context    $context
    ) {
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('mst_email_queue', 'queue_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Mirasvit\Email\Model\Queue $object */

        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        if (!$object->hasStatus()) {
            $object->setStatus(QueueInterface::STATUS_PENDING)
                ->setStatusMessage(__('Default status'));
        }

        if ($object->hasData('args')) {
            $object->setArgsSerialized($this->serializer->serialize($object->getData('args')));
        }

        $this->prepareHistory($object);

        if (!$object->getUniqHash()) {
            $object->setUniqHash(hash('sha256', $object->getUniqKey().$object->getCreatedAt()));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * Prepare history string
     *
     * @param \Mirasvit\Email\Model\Queue $object
     * @return $this
     */
    protected function prepareHistory($object)
    {
        if ($object->getOrigData('status') != $object->getData('status')) {
            $newStatus = $object->getData('status');

            $historyMessage = __("%1 - status changed to '%2'", date('M d, Y H:i:s'), $newStatus);
            if ($object->hasData('status_message')) {
                $historyMessage .= ' [' . $object->getData('status_message') . ']';
            }

            $history = $object->getData('history');

            $history .= $historyMessage . PHP_EOL;

            $object->setData('history', $history);
        }

        return $this;
    }

    /**
     * Load queue by unique key
     *
     * @param AbstractModel $object
     * @param string        $value
     * @return $this
     */
    protected function loadByHash($object, $value)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('uniq_key = ? OR uniq_hash = ?', $value, $value)
            ->limit(1);

        $data = $this->getConnection()->fetchRow($select);

        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (is_numeric($value)) {
            return parent::load($object, $value);
        } else {
            $this->loadByHash($object, $value);
        }

        return $this;
    }
}
