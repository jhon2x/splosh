<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Block\Adminhtml\Report\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Credentials extends Generic implements TabInterface
{
    // var \Sectionio\Metrics\Helper\Data $helper
    protected $helper;
    /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
    protected $settingsFactory;
    /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
    protected $accountFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Sectionio\Metrics\Helper\Data $helper
     * @param \Sectionio\Metrics\Model\SettingsFactory $settingsFactory
     * @param \Sectionio\Metrics\Model\AccountFactory $accountFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Sectionio\Metrics\Helper\Data $helper,
        \Sectionio\Metrics\Model\SettingsFactory $settingsFactory,
        \Sectionio\Metrics\Model\AccountFactory $accountFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;
        $this->settingsFactory = $settingsFactory;
        $this->accountFactory = $accountFactory;
        $this->setUseContainer(true);
    }

    private function prepareLoginForm($form, $settingsFactory)
    {

        $fieldset = $form->addFieldset(
            'edit_form_fieldset_settings',
            ['legend' => $this->helper->getCopy('credentials:fieldset-legend', 'section.io Account Credentials')]
        );

        $placeholder = $fieldset->addField('label', 'hidden', [
            'value' => '_placeholder',
        ]);

        $url = $this->getUrl('*/*/index', ['_query' => ['form' => 'register', 'tab' => 'credentials']]);
        $copy = $this->helper->getCopy('credentials:login-page-message', 'Please enter the credentials as provided by section.io.  If you haven\'t signed up yet you can <a href="#regolink">register here</a>. <br /><br />For questions or assistance, please <a href="https://community.section.io/tags/magento" target=\"_blank\">click here</a>.');
        $copy = str_replace('#regolink', $url, $copy);
        $placeholder->setBeforeElementHtml('
            <div class="messages">
                <div class="message message-notice">' . $copy . '</div>
            </div>
        ');

        // general_id field (hidden)
        $fieldset->addField(
            'general_id',
            'hidden',
            [
                'name' => 'general_id',
                'value' => $settingsFactory->getData('general_id')
            ]
        );

        // user_name field
        $fieldset->addField(
            'user_name',
            'text',
            [
                'name' => 'user_name',
                'label' => __('User Name'),
                'title' => __('User Name'),
                'style' => 'width:75%',
                'required' => true,
                'value' => $settingsFactory->getData('user_name')
            ]
        );

        // password field
        $password = $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'password',
                'label' => __('Password'),
                'title' => __('Password'),
                'style' => 'width:75%',
                'required' =>  ('' == $settingsFactory->getData('password') ? true : false),
                'value' => ''
            ]
        );

        // button to fetch info field
        $fieldset->addField(
            'save_info',
            'submit',
            [
                'value' => $this->helper->getCopy('credentials:save-button-value', 'Save Settings'),
                'class' => 'action-save action-secondary',
                'style' => 'width:auto'
            ]
        );
    }

    private function prepareRegistrationForm($form, $settingsFactory)
    {

        $fieldset = $form->addFieldset(
            'edit_form_fieldset_registration',
            ['legend' => $this->helper->getCopy('credentials:fieldset-legend', 'section.io Account Credentials')]
        );

        $placeholder = $fieldset->addField('register_label', 'hidden', [
            'value' => '_placeholder',
        ]);

        $objectManager = $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $user = $objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();

        $url = $this->getUrl('*/*/index', ['_query' => ['form' => 'login', 'tab' => 'credentials']]);
        $copy = $this->helper->getCopy('credentials:register-page-message', 'Fill in your details to register for section.io.  If you have already registered, <a href="#loginlink">login here</a>.<br /><br />For questions or assistance, please <a href="https://community.section.io/tags/magento" target=\"_blank\">click here</a>.');
        $copy = str_replace('#loginlink', $url, $copy);
        $placeholder->setBeforeElementHtml('
            <div class="messages">
                <div class="message message-notice">' . $copy . '</div>
            </div>
        ');

        // general_id field (hidden)
        $fieldset->addField(
            'general_id',
            'hidden',
            [
                'name' => 'general_id',
                'value' => $settingsFactory->getData('general_id')
            ]
        );

        $first_name = $this->getRequest()->getParam('first_name');
        if (!$first_name && $user) {
            $first_name = $user->getFirstname();
        }

        $last_name = $this->getRequest()->getParam('last_name');
        if (!$last_name && $user) {
            $last_name = $user->getLastname();
        }

        $user_name = $this->getRequest()->getParam('user_name');
        if (!$user_name && $user) {
            $user_name = $user->getEmail();
        }

        $fieldset->addField(
            'register_first_name',
            'text',
            [
                'name' => 'first_name',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'style' => 'width:75%',
                'required' => true,
                'value' => $first_name
            ]
        );

        $fieldset->addField(
            'register_last_name',
            'text',
            [
                'name' => 'last_name',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'style' => 'width:75%',
                'required' => true,
                'value' => $last_name
            ]
        );

        $fieldset->addField(
            'register_company',
            'text',
            [
                'name' => 'company',
                'label' => __('Company'),
                'title' => __('Company'),
                'style' => 'width:75%',
                'required' => false,
                'value' => $this->getRequest()->getParam('company')
            ]
        );

        $fieldset->addField(
            'register_phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Phone'),
                'title' => __('Phone'),
                'style' => 'width:75%',
                'required' => true,
                'value' => $this->getRequest()->getParam('phone')
            ]
        );

        // user_name field
        $fieldset->addField(
            'register_email',
            'text',
            [
                'name' => 'user_name',
                'label' => __('Email Address'),
                'title' => __('Email Address'),
                'style' => 'width:75%',
                'required' => true,
                'value' => $user_name
            ]
        );

        // password field
        $password = $fieldset->addField(
            'register_password',
            'password',
            [
                'name' => 'password',
                'label' => __('Password'),
                'title' => __('Password'),
                'style' => 'width:75%',
                'required' => true,
                'value' => ''
            ]
        );

        // confirm password field
        $confirm_password = $fieldset->addField(
            'register_confirm_password',
            'password',
            [
                'name' => 'confirm_password',
                'label' => __('Confirm Password'),
                'title' => __('Confirm Password'),
                'style' => 'width:75%',
                'required' => true,
                'value' => ''
            ]
        );

        // button to fetch info field
        $fieldset->addField(
            'register',
            'submit',
            [
                'name' => 'register',
                'value' => $this->helper->getCopy('credentials:register-button-value', 'Register'),
                'class' => 'action-save action-secondary',
                'style' => 'width:auto'
            ]
        );

        $placeholder = $fieldset->addField('tandclabel', 'hidden', [
            'value' => '_placeholder',
        ]);

        $copy = $this->helper->getCopy('credentials:termsandconditions', 'By registering you are agreeing to the <a href="https://www.section.io/legal-stuff/terms-and-conditions/" target="_blank">section.io Terms & Conditions</a>');
        $placeholder->setBeforeElementHtml('<div class="message">' . $copy . '</div>');
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
        $this->setTitle(__('section.io Account Credentials'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
        $settingsFactory = $this->settingsFactory->create()->getCollection()->getFirstItem();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_settings', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form_name = $this->getRequest()->getParam('form');

        if ($form_name == 'register') {
            $this->prepareRegistrationForm($form, $settingsFactory);
        } else {
            $this->prepareLoginForm($form, $settingsFactory);
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
        return __('section.io Account Credentials');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('section.io Account Credentials');
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

    /**
     * Get active account
     *
     * @param int $general_id
     *
     * @return string
     */
    public function getActiveAccountByGeneralId($general_id)
    {
        /** @var \Sectionio\Metrics\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->accountFactory->create()->getCollection();
        $collection->addFieldToFilter('general_id', ['eq' => $general_id]);
        $collection->addFieldToFilter('is_active', ['eq' => '1']);
        /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
        $accountFactory = $collection->getFirstItem();
        /** @var int $account_id */
        if ($account_id = $accountFactory->getData('account_id')) {
            return $account_id;
        }
        return false;
    }
}
