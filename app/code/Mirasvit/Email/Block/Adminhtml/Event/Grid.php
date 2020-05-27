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



namespace Mirasvit\Email\Block\Adminhtml\Event;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\TriggerRepositoryInterface;
use Mirasvit\Event\Ui\Event\Source\Event as SourceEvent;
use Mirasvit\Event\Api\Data\EventInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class Grid extends ExtendedGrid
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var SourceEvent
     */
    protected $sourceEvent;

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var TriggerRepositoryInterface
     */
    private $triggerRepository;

    /**
     * @param TriggerRepositoryInterface $triggerRepository
     * @param EventRepositoryInterface   $eventRepository
     * @param SourceEvent                $sourceEvent
     * @param Context                    $context
     * @param BackendHelper              $backendHelper
     */
    public function __construct(
        TriggerRepositoryInterface $triggerRepository,
        EventRepositoryInterface $eventRepository,
        SourceEvent $sourceEvent,
        Context $context,
        BackendHelper $backendHelper
    ) {
        $this->triggerRepository = $triggerRepository;
        $this->eventRepository = $eventRepository;
        $this->sourceEvent = $sourceEvent;
        $this->context = $context;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('email_event_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->eventRepository->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(EventInterface::ID, [
            'header' => __('ID'),
            'type'   => 'number',
            'index'  => EventInterface::ID,
        ]);

        $this->addColumn(EventInterface::IDENTIFIER, [
            'header'  => __('Event'),
            'index'   => EventInterface::IDENTIFIER,
            'type'    => 'options',
            'options' => $this->sourceEvent->toHash()
        ]);

        $this->addColumn('message', [
            'header'   => __('Message'),
            'index'    => EventInterface::PARAMS_SERIALIZED,
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'Mirasvit\Email\Block\Adminhtml\Event\Grid\Renderer\Message',
        ]);

        $this->addColumn(EventInterface::PARAMS_SERIALIZED, [
            'header'   => __('Arguments'),
            'index'    => EventInterface::PARAMS_SERIALIZED,
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'Mirasvit\Email\Block\Adminhtml\Event\Grid\Renderer\Args',
        ]);

        $this->addColumn(EventInterface::CREATED_AT, [
            'header' => __('Created At'),
            'index'  => EventInterface::CREATED_AT,
            'type'   => 'datetime',
        ]);

        $this->addColumn('triggers', [
            'header'   => __('Triggers'),
            'index'    => 'triggers',
            'renderer' => 'Mirasvit\Email\Block\Adminhtml\Event\Grid\Renderer\Triggers',
            'filter'   => false,
            'sortable' => false,
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_ids', [
                'header'     => __('Store'),
                'index'      => 'store_ids',
                'type'       => 'store',
                'store_all'  => true,
                'store_view' => true,
                'sortable'   => false
            ]);
        }

        $this->addColumn('action', [
            'header'    => __('Actions'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Reset & Process'),
                    'url'     => ['base' => '*/*/reset'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Delete'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => 'id',
                ],
            ],
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField(EventInterface::ID);
        $this->getMassactionBlock()->setFormFieldName(EventInterface::ID);

        $this->getMassactionBlock()->addItem('delete', [
            'label'    => __('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => __('Are you sure?')
        ]);

        $triggers = $this->triggerRepository->getCollection()
            ->addFieldToFilter(TriggerInterface::RULE_SERIALIZED, ['neq' => '[]'])
            ->toOptionArray();

        $this->getMassactionBlock()->addItem('validate', [
            'label' => __('Validate'),
            'url'   => $this->getUrl('*/*/massValidate', ['_current' => true]),
            'additional' => [
                'trigger' => [
                    'name'   => TriggerInterface::ID,
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => __('Select Trigger'),
                    'values' => $triggers
                ]
            ]
        ]);


        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
