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



namespace Mirasvit\Email\Block\Adminhtml\Queue;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Model\Queue;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Mirasvit\Email\Model\ResourceModel\Trigger\CollectionFactory as TriggerCollectionFactory;

class Grid extends GridExtended
{
    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var TriggerCollectionFactory
     */
    protected $triggerCollectionFactory;
    /**
     * @var \Magento\Framework\Url
     */
    private $frontUrlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Url               $frontUrlBuilder
     * @param QueueCollectionFactory               $queueQueueCollectionFactory
     * @param TriggerCollectionFactory             $triggerCollectionFactory
     * @param Context                              $context
     * @param BackendHelper                        $backendHelper
     */
    public function __construct(
        \Magento\Framework\Url $frontUrlBuilder,
        QueueCollectionFactory $queueQueueCollectionFactory,
        TriggerCollectionFactory $triggerCollectionFactory,
        Context $context,
        BackendHelper $backendHelper
    ) {
        $this->frontUrlBuilder = $frontUrlBuilder;
        $this->queueCollectionFactory = $queueQueueCollectionFactory;
        $this->triggerCollectionFactory = $triggerCollectionFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('email_queue_grid');
        $this->setDefaultSort('queue_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->queueCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', [
            'header' => __('ID'),
            'type'   => 'number',
            'index'  => 'queue_id',
        ]);

        $this->addColumn('status', [
            'header'  => __('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => [
                QueueInterface::STATUS_PENDING      => __('Ready to go'),
                QueueInterface::STATUS_SENT         => __('Sent'),
                QueueInterface::STATUS_CANCELED     => __('Canceled'),
                QueueInterface::STATUS_ERROR        => __('Error'),
                QueueInterface::STATUS_MISSED       => __('Missed'),
                QueueInterface::STATUS_UNSUBSCRIBED => __('Unsubcribed'),
            ],
        ]);

        $this->addColumn('trigger_id', [
            'header'  => __('Trigger'),
            'index'   => 'trigger_id',
            'type'    => 'options',
            'options' => $this->triggerCollectionFactory->create()->toOptionHash()
        ]);

        $this->addColumn('scheduled_at', [
            'header' => __('Scheduled At'),
            'type'   => 'datetime',
            'index'  => 'scheduled_at',
        ]);

        $this->addColumn('sent_at', [
            'header' => __('Sent At'),
            'type'   => 'datetime',
            'index'  => 'sent_at',
        ]);

        $this->addColumn('recipient_email', [
            'header' => __('Recipient Email'),
            'index'  => 'recipient_email',
        ]);

        $this->addColumn('recipient_name', [
            'header' => __('Recipient Name'),
            'index'  => 'recipient_name',
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Cancel'),
                    'url'     => ['base' => '*/*/cancel'],
                    'field'   => 'id',
                ],
                //[
                //    'caption' => __('Send'),
                //    'url'     => ['base' => '*/*/send'],
                //    'field'   => 'id',
                //],
                [
                    'caption' => __('Reset'),
                    'url'     => ['base' => '*/*/reset'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => 'id',
                    'confirm' => __('Are you sure?'),
                ],
            ],
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('queue_id');
        $this->getMassactionBlock()->setFormFieldName('queue');

        $this->getMassactionBlock()->addItem('cancel', [
            'label'   => __('Cancel'),
            'url'     => $this->getUrl('*/*/massCancel'),
            'confirm' => __('Are you sure?'),
        ]);

        $this->getMassactionBlock()->addItem('send', [
            'confirm' => __('Are you sure?'),
            'label'   => __('Send'),
            //'url'   => $this->getUrl('*/*/massSend', ['form_key' => $formKey->getFormKey()]),
            'url'     => $this->frontUrlBuilder->getUrl('email/action/sendAll', [
                'form_key' => $this->formKey->getFormKey()
            ]),
        ]);

        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        $statuses = [
            QueueInterface::STATUS_PENDING  => __('Pending'),
            QueueInterface::STATUS_SENT     => __('Sent'),
            QueueInterface::STATUS_CANCELED => __('Canceled'),
            QueueInterface::STATUS_ERROR    => __('Error'),
            QueueInterface::STATUS_MISSED   => __('Missed'),
        ];
        array_unshift($statuses, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()->addItem('status', [
            'label'      => __('Change status'),
            'url'        => $this->getUrl('*/*/massStatus', ['_current' => true]),
            'additional' => [
                'visibility' => [
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => __('Status'),
                    'values' => $statuses,
                ],
            ],
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', ['id' => $row->getId()]);
    }
}
