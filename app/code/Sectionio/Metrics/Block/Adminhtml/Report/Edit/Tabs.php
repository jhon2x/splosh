<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Block\Adminhtml\Report\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
    protected $settingsFactory;
    /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
    protected $accountFactory;
    /** @var \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory */
    protected $applicationFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Sectionio\Metrics\Model\SettingsFactory $settingsFactory
     * @param \Sectionio\Metrics\Model\AccountFactory $accountFactory
     * @param \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Sectionio\Metrics\Model\SettingsFactory $settingsFactory,
        \Sectionio\Metrics\Model\AccountFactory $accountFactory,
        \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->settingsFactory = $settingsFactory;
        $this->accountFactory = $accountFactory;
        $this->applicationFactory = $applicationFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('report_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Section.io Account Settings'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $selected_tab = $this->getRequest()->getParam('tab');

        // account credentials tab
        $this->addTab(
            'report_edit_tabs_credentials',
            [
                'label' => __('Account Credentials'),
                'title' => __('Account Credentials'),
                'content' => $this->getLayout()->createBlock(
                    'Sectionio\Metrics\Block\Adminhtml\Report\Edit\Tab\Credentials'
                )
                ->toHtml(),
                'active' => $selected_tab == 'credentials'
            ]
        );

        /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
        $settingsFactory = $this->settingsFactory->create()->getCollection()->getFirstItem();

        // only display if account credentials are saved
        if ($settingsFactory->getData('general_id')) {
            // account settings tab
            $this->addTab(
                'report_edit_tabs_settings',
                [
                    'label' => __('Management'),
                    'title' => __('Default Account / Application'),
                    'content' => $this->getLayout()->createBlock(
                        'Sectionio\Metrics\Block\Adminhtml\Report\Edit\Tab\Settings'
                    )
                    ->toHtml(),
                    'active' => !$selected_tab || $selected_tab == 'settings'
                ]
            );
        }

        /** @var int $account_id */
        $account_id = $this->accountFactory->create()->getCollection()
            ->addFieldToFilter('is_active', ['eq' => '1'])
            ->getFirstItem()
            ->getData('id');
        /** @var int $application_id */
        $application_id = $this->accountFactory->create()->getCollection()
            ->addFieldToFilter('is_active', ['eq' => '1'])
            ->getFirstItem()
            ->getData('id');
        // only show tab if default account_id and application_id exist
        if ($account_id and $application_id) {
            $this->addTab(
                'report_edit_tabs_metrics',
                [
                    'label' => __('View Site Metrics'),
                    'title' => __('View Site Metrics'),
                    'content' => $this->getLayout()->createBlock(
                        'Sectionio\Metrics\Block\Adminhtml\Report\Edit\Tab\Metrics'
                    )->toHtml()
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
