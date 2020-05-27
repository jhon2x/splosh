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



namespace Mirasvit\EmailDesigner\Block\Adminhtml\Theme;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var ThemeCollectionFactory
     */
    protected $themeCollectionFactory;

    /**
     * Constructor
     *
     * @param ThemeCollectionFactory $themeCollectionFactory
     * @param Context                $context
     * @param BackendHelper          $backendHelper
     */
    public function __construct(
        ThemeCollectionFactory $themeCollectionFactory,
        Context $context,
        BackendHelper $backendHelper
    ) {
        $this->themeCollectionFactory = $themeCollectionFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('email_designer_theme_grid');
        $this->setDefaultSort(ThemeInterface::ID);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->themeCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(ThemeInterface::ID, [
            'header' => __('ID'),
            'index'  => ThemeInterface::ID,
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'index'  => 'title',
        ]);

        $this->addColumn('action', [
            'header'    => __('Actions'),
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Edit'),
                    'url'     => ['base' => '*/*/edit'],
                    'field'   => ThemeInterface::ID,
                ],
//              [
//                    'caption' => __('Export'),
//                    'url'     => ['base' => '*/*/export'],
//                    'field'   => ThemeInterface::ID,
//              ],
                [
                    'caption' => __('Remove'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => ThemeInterface::ID,
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
        return $this->getUrl('*/*/edit', [ThemeInterface::ID => $row->getId()]);
    }
}
