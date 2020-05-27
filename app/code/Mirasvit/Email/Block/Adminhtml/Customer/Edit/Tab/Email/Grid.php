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


namespace Mirasvit\Email\Block\Adminhtml\Customer\Edit\Tab\Email;

use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Mirasvit\Email\Model\ResourceModel\Trigger\CollectionFactory as TriggerCollectionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Mirasvit\Email\Model\Queue;

class Grid extends ExtendedGrid
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * @var TriggerCollectionFactory
     */
    protected $triggerCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param QueueCollectionFactory      $queueCollectionFactory
     * @param TriggerCollectionFactory    $triggerCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Context                     $context
     * @param BackendHelper               $backendHelper
     * @param Registry                    $registry
     */
    public function __construct(
        QueueCollectionFactory $queueCollectionFactory,
        TriggerCollectionFactory $triggerCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Context $context,
        BackendHelper $backendHelper,
        Registry $registry
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->triggerCollectionFactory = $triggerCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;

        parent::__construct($context, $backendHelper);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('queueGrid');
        $this->setDefaultSort('queue_id');
        $this->setDefaultDir('desc');

        $this->setEmptyText(__('No Emails Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customer = $this->customerRepository->getById(
            $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
        /** @var $collection \Mirasvit\Email\Model\ResourceModel\Queue\Collection */
        $collection = $this->queueCollectionFactory->create()
            ->addFieldToFilter('recipient_email', $customer->getEmail());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', [
            'header'   => __('ID'),
            'type'     => 'number',
            'index'    => 'queue_id',
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('status', [
            'header'   => __('Status'),
            'index'    => 'status',
            'type'     => 'options',
            'options'  => [
                QueueInterface::STATUS_PENDING      => __('Ready to go'),
                QueueInterface::STATUS_SENT         => __('Sent'),
                QueueInterface::STATUS_CANCELED     => __('Canceled'),
                QueueInterface::STATUS_ERROR        => __('Error'),
                QueueInterface::STATUS_MISSED       => __('Missed'),
                QueueInterface::STATUS_UNSUBSCRIBED => __('Unsubcribed'),
            ],
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('trigger_id', [
            'header'   => __('Trigger'),
            'index'    => 'trigger_id',
            'type'     => 'options',
            'options'  => $this->triggerCollectionFactory->create()->toOptionHash(),
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('scheduled_at', [
            'header'   => __('Scheduled At'),
            'type'     => 'datetime',
            'index'    => 'scheduled_at',
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('sent_at', [
            'header'   => __('Sent At'),
            'type'     => 'datetime',
            'index'    => 'sent_at',
            'filter'   => false,
            'sortable' => false,
        ]);

        return parent::_prepareColumns();
    }
}
