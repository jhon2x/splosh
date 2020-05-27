<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Block\Adminhtml\Report\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Metrics extends Generic implements TabInterface
{
    /** @var \Sectionio\Metrics\Helper\Data $helper */
    protected $helper;
    /** @var \Sectionio\Metrics\Helper\State $helper */
    protected $state;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Sectionio\Metrics\Helper\Data $helper
     * @param \Sectionio\Metrics\Helper\State $state
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Sectionio\Metrics\Helper\Data $helper,
        \Sectionio\Metrics\Helper\State $state,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;
        $this->state = $state;
        $this->setUseContainer(true);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_settings');
        $this->setTitle(__('section.io Site Metrics'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var $count */
        $count = 0;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_settings', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        /** @var int $account_id */
        $account_id = $this->state->getAccountId();
        /** @var int $application_id */
        $application_id = $this->state->getApplicationId();

        /** @var array() $data */
        if ($data = $this->helper->getMetrics($account_id, $application_id)) {
            if (isset($data['intro'])) {
                $intro = $form->addFieldset(
                    'edit_form_fieldset_intro',
                    []
                );
                $placeholder = $intro->addField('label', 'hidden', [
                    'value' => __('section.io Metrics'),
                ]);
                $placeholder->setBeforeElementHtml('
                    <div>
                        <div>' . $data['intro'] . '</div>
                    </div>
                ');
                $intro = $data['intro'];
                unset($data['intro']);
            }

            // loop through $data
            foreach ($data as $chart) {
                // render metric title
                $chartFieldset[$count] = $form->addFieldset(
                    'edit_form_fieldset_metrics_' . $count,
                    ['legend' => __($chart['title'])]
                );
                // render metric
                $field[$count] = $chartFieldset[$count]->addField('sample_' . $count, 'hidden', [
                    'value' => __($chart['title']),
                ]);
                if (isset($chart['apertureLink'])) {
                    $field[$count]->setAfterElementHtml('
                        <div>' . $chart['help'] . '<a href="' . $chart['docs'] . '" style="color: #FF0000" target="_blank">   Read more.</a></div><br>
                        <img src="data:image/png;base64,' . $chart['chart'] . '">' .
                        '<div><br>View this in the <a href="https://aperture.section.io/account/' . $account_id . '/application/' . $application_id . '/graphite-web" target="_blank">section.io</a> console.</div>
                    ');
                } else {
                    $field[$count]->setAfterElementHtml('
                        <div>' . $chart['help'] . '<a href="' . $chart['docs'] . '" style="color: #FF0000" target="_blank">   Read more.</a></div><br>
                        <img src="data:image/png;base64,' . $chart['chart'] . '">
                    ');
                }
                // increment $count
                $count ++;
            }
        } // unable to retrieve content
        else {
            /** @var string $ectionio_url */
            $sectionio_url = 'https://aperture.section.io/';

            // change section.io link to use default account
            if ($account_id) {
                $sectionio_url = 'https://aperture.section.io/account/' . $account_id . '/';
            }

            $fieldset = $form->addFieldset(
                'edit_form_fieldset_settings',
                ['legend' => __('section.io Site Metrics')]
            );

            $message = $fieldset->addField('label', 'hidden', [
                'value' => __('section.io Account Credentials'),
            ]);

            $message->setBeforeElementHtml('
                <div class="messages">
                    <div class="message message-notice">
                        Unable to retrieve content at this time.  Please make sure to select a default account and application.  For questions or assistance, please <a href="' . $sectionio_url . '" target="_blank">click here.</a>.
                    </div>
                </div>
            ');
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('section.io Site Metrics');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('section.io Site Metrics');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
