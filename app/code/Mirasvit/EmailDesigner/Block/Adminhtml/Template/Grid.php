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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;


class Grid extends ExtendedGrid
{
    /**
     * @var TemplateCollectionFactory
     */
    protected $templateCollectionFactory;

    /**
     * Constructor
     *
     * @param TemplateCollectionFactory $templateCollectionFactory
     * @param Context                   $context
     * @param BackendHelper             $backendHelper
     */
    public function __construct(
        TemplateCollectionFactory $templateCollectionFactory,
        Context $context,
        BackendHelper $backendHelper
    ) {
        $this->templateCollectionFactory = $templateCollectionFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('emaildesing_template_grid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->templateCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('template_id', [
            'header' => __('ID'),
            'index'  => 'template_id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'index'  => 'title'
        ]);

        $this->addColumn('action', [
            'header'    => __('Actions'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Edit'),
                    'url'     => ['base' => '*/*/edit'],
                    'field'   => TemplateInterface::ID,
                ],
                [
                    'caption' => __('Remove'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => TemplateInterface::ID,
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
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', [TemplateInterface::ID => $row->getId()]);
    }
}
