<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;

class Save extends Action
{
    /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
    protected $settingsFactory;
    /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
    protected $accountFactory;
    /** @var \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory */
    protected $applicationFactory;
    /** @var PageCache $pageCacheConfig */
    protected $pageCacheConfig;
    /** @var \Sectionio\Metrics\Helper\Data $helper */
    protected $helper;
    /** @var \Sectionio\Metrics\Helper\State $state */
    protected $state;
    /** @var \Sectionio\Metrics\Helper\Aperture $aperture */
    protected $aperture;

    /**
     * @param Magento\Backend\App\Action\Context $context
     * @param \Magento\PageCache\Model\Config $pageCacheConfig
     * @param \Sectionio\Metrics\Model\SettingsFactory $settingsFactory
     * @param \Sectionio\Metrics\Model\AccountFactory $accountFactory
     * @param \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory
     * @param \Sectionio\Metrics\Helper\Data $helper
     * @param \Sectionio\Metrics\Helper\State $state
     * @param \Sectionio\Metrics\Helper\Aperture $aperture
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\PageCache\Model\Config $pageCacheConfig,
        \Sectionio\Metrics\Model\SettingsFactory $settingsFactory,
        \Sectionio\Metrics\Model\AccountFactory $accountFactory,
        \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory,
        \Sectionio\Metrics\Helper\Data $helper,
        \Sectionio\Metrics\Helper\State $state,
        \Sectionio\Metrics\Helper\Aperture $aperture
    ) {
        parent::__construct($context);
        $this->pageCacheConfig = $pageCacheConfig;
        $this->settingsFactory = $settingsFactory;
        $this->accountFactory = $accountFactory;
        $this->applicationFactory = $applicationFactory;
        $this->helper = $helper;
        $this->aperture = $aperture;
        $this->state = $state;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('vcl_btn') != null) {
            return $this->updateVarnishConfiguration();
        }
        if ($this->getRequest()->getParam('certificate_btn') != null) {
            return $this->certificateChallenge();
        }
        if ($this->getRequest()->getParam('verify_btn') != null) {
            return $this->verifyApplication();
        }
        return $this->saveConfiguration($this->getRequest()->getParam('register'));
    }

    public function saveConfiguration($register)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
        $settingsFactory = $this->settingsFactory->create();
        /** @var int $account_id */
        $account_id = $this->getRequest()->getParam('account_id');
        /** @var boolean $update_flag */
        $update_flag = false;

        if ($register) {
            $first_name = $this->getRequest()->getParam('first_name');
            $last_name = $this->getRequest()->getParam('last_name');
            $company = $this->getRequest()->getParam('company');
            $phone = $this->getRequest()->getParam('phone');
            $user_name = $this->getRequest()->getParam('user_name');

            $password = $this->getRequest()->getParam('password');
            $confirm_password = $this->getRequest()->getParam('confirm_password');

            $return_query_string = [
                'form' => 'register',
                'tab' => 'credentials',
                'first_name' => $first_name,
                'last_name' => $last_name,
                'company' => $company,
                'phone' => $phone,
                'user_name' => $user_name
            ];
            if ($password != $confirm_password) {
                $this->messageManager->addError(__('Password and Confirm Password must match'));
                return $resultRedirect->setPath('*/*/index', ['_query' => $return_query_string]);
            }

            $result = $this->aperture->register($first_name, $last_name, $company, $phone, $user_name, $password);

            $result_content = json_decode($result['body_content']);
            if (isset($result_content->errors)) {
                foreach ($result_content->errors as $error) {
                    $this->messageManager->addError(__($error));
                }
                return $resultRedirect->setPath('*/*/index', ['_query' => $return_query_string]);
            }
        }

        /** @var int $general_id */
        if ($general_id = $this->getRequest()->getParam('general_id')) {
            // loads model if available
            $settingsFactory->load($general_id);
        } // new account - set update_flag
        else {
            $update_flag = true;
        }
        // only update on change
        if ($user_name = $this->getRequest()->getParam('user_name')) {
            if ($user_name != $settingsFactory->getData('user_name')) {
                $account_id = null;
                $this->helper->cleanSettings();
                $update_flag = true;
                $settingsFactory->setData('user_name', $user_name);
            }
        }
        // only update on change
        if ($password = $this->getRequest()->getParam('password')) {
            if ($password != '') {
                $account_id = null;
                $this->helper->cleanSettings();
                $update_flag = true;
                $this->state->savePassword($settingsFactory, $password);
            }
        }
        // update settings
        if ($update_flag) {
            // save updated settings
            $settingsFactory->save();
            // fetch new account and application data
            return $resultRedirect->setPath('metrics/report/fetchInfo');
        } else {
            // if $account_id
            if ($account_id) {
                // set default account
                $this->helper->setDefaultAccount($account_id);
                // if exists
                if ($application_id = $this->getRequest()->getParam('application_id' . $account_id)) {
                    // set default application
                    $this->helper->setDefaultApplication($application_id);
                } else {
                    // clear default application
                    $this->helper->clearDefaultApplication();
                }
            } else {
                // clear default account and application
                $this->helper->clearDefaultAccount();
                $this->helper->clearDefaultApplication();
            }
            $this->messageManager
                ->addSuccess(__('You have successfully updated the account information.'));
            return $resultRedirect->setPath('metrics/report/index');
        }
    }

    public function certificateChallenge()
    {
        /** @var int $account_id */
        $account_id = $this->state->getAccountId();
        /** @var string $hostname */
        $hostname = $this->state->getHostname();

        $result = $this->aperture->renewCertificate($account_id, $hostname);
        if ($result['http_code'] == 200) {
            $this->messageManager->addSuccess(__('A new certificate has been added to your domain ' . $hostname));
        } else {
            $this->messageManager->addError(__('Error validating ownership for certificate for domain ' . $hostname . '. Please check that this Magento installation is exposed to the internet on port 80.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('metrics/report/index');
    }

    public function updateVarnishConfiguration()
    {
        /** @var int $account_id */
        $account_id = $this->state->getAccountId();
        /** @var int $application_id */
        $application_id = $this->state->getApplicationId();
        /** @var string $environment_name */
        $environment_name = $this->state->getEnvironmentName();
        /** @var string $proxy_image*/
        $proxy_version = $this->helper->getProxyVersion($account_id, $application_id);

        $major_release = $this->helper->getMajorRelease($proxy_version);

        /** Extract the generated VCL code appropriate for their version*/
        $vcl = $this->helper->getCorrectVCL($this->pageCacheConfig, $major_release);

        $result = $this->aperture->updateProxyConfiguration($account_id, $application_id, $environment_name, 'varnish', $vcl, 'MagentoTurpentine');

        if ($result['http_code'] == 200) {
            $this->messageManager->addSuccess(__('You have successfully updated varnish configuration.'));
        } else {
            $this->messageManager->addError(__('Error updating varnish configuration, upstream returned HTTP ' . $result['http_code'] . '.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('metrics/report/index');
    }

    public function verifyApplication()
    {
        /** @var int $account_id */
        $account_id = $this->state->getAccountId();
        /** @var int $account_id */
        $application_id = $this->state->getApplicationId();
        /** @var string $hostname */
        $hostname = $this->state->getHostname();

        $result = $this->aperture->verifyEngaged($account_id, $application_id);
        if ($result['http_code'] == 200) {
            $body = json_decode($result['body_content'], true);
            if ($body['is_engaged'] == true) {
                $this->messageManager->addSuccess('Success! DNS for ' . $hostname . ' has been configured correctly.');
            } else {
                $this->messageManager->addError('DNS verification failed for ' . $hostname . '.');
            }
        } else {
            $this->messageManager->addError('Unexpected response from server HTTP ' . $result['http_code']);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('metrics/report/index');
    }
}
