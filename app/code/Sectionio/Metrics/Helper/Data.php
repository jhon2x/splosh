<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
    protected $settingsFactory;
    /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
    protected $accountFactory;
    /** @var \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory */
    protected $applicationFactory;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;
    /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
    protected $encryptor;
    // var \Magento\Framework\Filesystem\DirectoryList $directoryList
    protected $directoryList;
    // var \Magento\Store\Model\StoreManagerInterface $storeManager
    protected $storeManager;
    /** @var \Sectionio\Metrics\Helper\State $state */
    protected $state;
    /** @var \Sectionio\Metrics\Helper\Aperture $aperture */
    protected $aperture;


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Sectionio\Metrics\Model\SettingsFactory $settingsFactory
     * @param \Sectionio\Metrics\Model\AccountFactory $accountFactory
     * @param \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Sectionio\Metrics\Helper\State $state
     * @param \Sectionio\Metrics\Helper\Aperture $aperture
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Sectionio\Metrics\Model\SettingsFactory $settingsFactory,
        \Sectionio\Metrics\Model\AccountFactory $accountFactory,
        \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Sectionio\Metrics\Helper\State $state,
        \Sectionio\Metrics\Helper\Aperture $aperture
    ) {
        parent::__construct($context);
        $this->settingsFactory = $settingsFactory;
        $this->accountFactory = $accountFactory;
        $this->applicationFactory = $applicationFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->encryptor = $encryptor;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->aperture = $aperture;
    }

    private function getPluginConfig()
    {
        $cache_expiry_seconds = 60 * 60; // 1 hour

        // http://blog.belvg.com/how-to-get-access-to-working-directories-in-magento-2-0.html
        $cache_dir = $this->directoryList->getPath('cache') . '/section.io';
        if (!is_dir($cache_dir)) {
            try {
                mkdir($cache_dir);
            } catch (\Exception $e) {
                $this->_logger->warning('Could not create cache directory. Skipping cache.', [ 'exception' => $e ]);
            }
        }
        $cached_config_file = $cache_dir . '/magento-section-io-plugin-config.json';

        if (is_file($cached_config_file)) {
            $mtime = filemtime($cached_config_file);
            if (time() - $mtime < $cache_expiry_seconds) {
                return file_get_contents($cached_config_file);
            }
        }

        /** @var string $service_url */
        $service_url = 'https://www.section.io/magento-section-io-plugin-config.json';

        // setup curl call
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // if response received
        if ($curl_response = curl_exec($ch)) {
            try {
                file_put_contents($cached_config_file, $curl_response);
            } catch (\Exception $e) {
                $this->_logger->warning('Could not write cache file. Skipping cache.', [ 'exception' => $e ]);
            }
            return $curl_response;
        }
    }

    // to find all the identifiers used with GetCopy, use:
    // $ grep -ho "getCopy([\"']\([^\"']\|\\[\"']\)*" --include=*.php -r /var/www/html/app/code/Sectionio/Metrics | cut -c10-
    public function getCopy($identifier, $default)
    {
        $decoded = json_decode($this->getPluginConfig(), true);
        $copy = [];
        if (array_key_exists('copy', $decoded)) {
            $copy = $decoded['copy'];
        }
        if (array_key_exists($identifier, $copy)) {
            return $copy[$identifier];
        }
        return $default;
    }

    /**
     * Retrieves the section.io site metrics
     *
     * @param int $account_id
     * @param int $application_id
     *
     * @return array()
     */
    public function getMetrics($account_id, $application_id)
    {

        /** @var array() $response */
        $response = [];
        /** @var int $count */
        $count = 0;

        // if response received
        if ($plugin_config = $this->getPluginConfig()) {
            if ($data = json_decode($plugin_config, true)) {
                // loop through return data
                foreach ($data as $key => $charts) {
                    if (is_array($charts)) {
                        // loop through each chart / graph
                        foreach ($charts as $chart) {
                            // make sure return data exists
                            if (isset($chart['url']) && isset($chart['title'])) {
                                /** @var string $url */
                                $url = str_replace('https://aperture.section.io/account/1/application/1/', '', $chart['url']);
                                /** @var string $service_url */
                                $service_url = ('https://aperture.section.io/account/' . $account_id . '/application/' . $application_id . '/' . $url);
                                // append time zone
                                $service_url .= '&tz=' . $this->scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                                /** @var object $image */
                                if ($image = $this->performCurl($service_url)['body_content']) {
                                    // build return array
                                    $response[$count]['title'] = $chart['title'];
                                    $response[$count]['chart'] = base64_encode($image);
                                    $response[$count]['help'] = $chart['help'];
                                    $response[$count]['docs'] = $chart['docs'];
                                    if (isset($chart['apertureLink'])) {
                                        $response[$count]['apertureLink'] = $chart['apertureLink'];
                                    }
                                    // increment count
                                    $count ++;
                                }
                            }
                        }
                    } elseif ($key == 'intro') {
                        if (is_string($charts)) {
                            $response['intro'] = $charts;
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Save the user's password encrypted in the database
     *
     * @param string $password
     *
     */
    public function savePassword($settingsFactory, $password)
    {
        $settingsFactory->setData('password', $this->encryptor->encrypt($password));
    }

    /**
     * Generate an aperture API URL
     *
     * @param array $parameters
     *
     */
    public function generateApertureUrl($parameters)
    {
        $url = 'https://aperture.section.io/api/v1';
        if (isset($parameters['accountId'])) {
            $url .= '/account/' . $parameters['accountId'];
        }

        if (isset($parameters['applicationId'])) {
            $url .= '/application/' . $parameters['applicationId'];
        }

        if (isset($parameters['environmentName'])) {
            $url .= '/environment/' . $parameters['environmentName'];
        }

        if (isset($parameters['proxyName'])) {
            $url .= '/proxy/' . $parameters['proxyName'];
        }

        if (isset($parameters['domain'])) {
            $url .= '/domain/' . $parameters['domain'];
        }

        if (isset($parameters['uriStem'])) {
            $url .= $parameters['uriStem'];
        }
        $this->_logger->debug($url);
        return $url;
    }

    /**
     * Perform Sectionio curl call
     *
     * @param string $service_url
     * @param array() $credentials
     * @param string $method
     * @param array() $payload
     *
     * @return array() $response
     */
    public function performCurl($service_url, $method = 'GET', $payload = null)
    {

        /** @var \Sectionio\Metrics\Model\SettingsFactory $settingsFactory */
        $settingsFactory = $this->settingsFactory->create()->getCollection()->getFirstItem();
        /** @var string $credentials */
        $credentials = ($settingsFactory->getData('user_name') . ':' . $this->encryptor->decrypt($settingsFactory->getData('password')));

        // setup curl call
         $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $service_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            /** @var string $json */
            $json = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($json)]);
        }

        // construct the response object from the curl info and the body response
        $curl_response = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        $curl_info['body_content'] = $curl_response;

        return $curl_info;
    }


    /**
     * Execute account and application api calls
     *
     * @param \Magento\Framework\Message\Manager $messageManager
     *
     * @return void
     */
    public function refreshApplications(&$messageManager, $account_id = null, $application_id = null)
    {

        $settingsFactory = $this->settingsFactory->create()->getCollection()->getFirstItem();
        $accountFactory = $this->accountFactory->create();
        $general_id = $settingsFactory->getData('general_id');
        $service_url;
        if (!$account_id && !$application_id) {
            $service_url = $this->generateApertureUrl(['uriStem' => '/account']);
        } else {
            $service_url = $this->generateApertureUrl(['accountId' => $account_id]);
        }

        // remove the existing accounts
        $this->cleanSettings();
        // perform account curl call
        $curl_response = $this->performCurl($service_url);
        $this->_logger->debug(print_r($service_url, true));
        if ($curl_response['http_code'] == 200) {
            $accountData = json_decode($curl_response['body_content'], true);

            if (!$accountData) {
                /** @var string $hostname */
                $hostname = $this->state->getHostname();
                /** @var string $service_url */
                $service_url = $this->generateApertureUrl([
                    'uriStem' => '/origin?hostName=' . $hostname
                ]);
                /** @var array $origin */
                $origin = json_decode($this->performCurl($service_url)['body_content'], true);
                /** @var string $origin_address */
                $origin_address = $origin['origin_detected'] ? $origin['origin'] : '192.168.35.10';
                /** @var array $payload */
                $payload = ['name' => $hostname, 'hostname' => $hostname, 'origin' => $origin_address, 'stackName' => 'varnish'];
                /** @var string $service_url */
                $service_url = $this->generateApertureUrl(['uriStem' => '/account/create']);
                /** @var array $account_response */
                $account_response = $this->performCurl($service_url, 'POST', $payload);
                /** @var string $account_content */
                $account_content = json_decode($account_response['body_content'], true);
                if ($account_response['http_code'] != 200) {
                    $messageManager
                        ->addError(__($account_content['message']));
                    return;
                }
                $accountData[] = $account_content;

                $this->_logger->info('Retrieving certificate started for ' . $hostname);
                $certificate_response = $this->aperture->renewCertificate($account_content['id'], $hostname);
                $this->_logger->info('Retrieving certificate finished for ' . $hostname . '  with result ' . $certificate_response['http_code']);
            }

            // Coerce into a multi-item array if it isn't one
            if (isset($accountData['id'])) {
                $accountData = [$accountData];
            }

            // loop through accounts discovered
            foreach ($accountData as $account) {
                /** @var int $id */
                $id = $account['id'];
                /** @var string $account_name */
                $account_name = $account['account_name'];
                /** @var int $account_id */
                if ($account_id = $this->updateAccount($general_id, $id, $account_name)) {
                    /** @var string $service_url */
                    if (!$application_id) {
                        $service_url = $this->generateApertureUrl(['accountId' => $id, 'uriStem' => '/application']);
                    } else {
                        $service_url = $this->generateApertureUrl(['accountId' => $id, 'applicationId' => $application_id]);
                    }
                    // perform application curl call
                    if ($applicationData = json_decode($this->performCurl($service_url)['body_content'], true)) {
                        //Coerce into multi-item array if single item is returned
                        if (isset($applicationData['id'])) {
                            $applicationData = [$applicationData];
                        }

                        // loop through available applications
                        foreach ($applicationData as $application) {
                            /** @var int $application_id */
                            $application_id = $application['id'];
                            /** @var string $application_name */
                            $application_name = $application['application_name'];
                            // record application results
                            $this->updateApplication($account_id, $application_id, $application_name);
                        }
                    }
                }
            }
            // successful results
            $messageManager
                ->addSuccess(__('You have successfully updated account and application data.  Please select your default account and application and save the selections.  Once complete, you will be able to view the Section.io site metrics.'));
        } // no account data
        else {
            $messageManager
                ->addError(__('No accounts discovered.  Please check your credentials and try again.'));
        }
    }

    /**
     * Process account result
     *
     * @param int $general_id
     * @param int $id
     * @param string $account_name
     *
     * @return int
     */
    public function updateAccount($general_id, $id, $account_name)
    {
        /** @var \Sectionio\Metrics\Model\AccountFactory $model */
        $model = $this->accountFactory->create();
        /** @var \Sectionio\Metrics\Model\ResourceModel\Account\Collection $collection */
        $collection = $model->getCollection();
        /** @var boolean $flag */
        $flag = true;
        // loop through $collection
        foreach ($collection as $account) {
            // if account already exists
            if ($account->getData('account_id') == $id) {
                $flag = false;
                break;
            }
        }
        // new account discovered
        if ($flag) {
            // set data
            $model->setData('general_id', $general_id);
            $model->setData('account_id', $id);
            $model->setData('account_name', $account_name);
            $model->setData('is_active', '0');
            // save account
            $model->save();
            // return unique id
            return ($model->getData('id'));
        }
        // no results
        return false;
    }

    /**
     * Process application result
     *
     * @param int $account_id
     * @param int $id
     * @param string $application_name
     *
     * @return void
     */
    public function updateApplication($account_id, $id, $application_name)
    {
        /** @var \Sectionio\Metrics\Model\ApplicationFactory $model */
        $model = $this->applicationFactory->create();
        /** @var \Sectionio\Metrics\Model\ResourceModel\Application\Collection $collection */
        $collection = $model->getCollection();
        /** @var boolean $flag */
        $flag = true;
        // loop through collection
        foreach ($collection as $application) {
            // if application already exists
            if ($application->getData('application_id') == $id) {
                $flag = false;
                break;
            }
        }
        // new application discovered
        if ($flag) {
            // set data
            $model->setData('account_id', $account_id);
            $model->setData('application_id', $id);
            $model->setData('application_name', $application_name);
            $model->setData('is_active', '0');
            // save application
            $model->save();
        }
    }

    /**
     * Clean current accounts
     *
     * @return void
     */
    public function cleanSettings()
    {
        /** @var \Sectionio\Metrics\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->accountFactory->create()->getCollection();
        // delete all existing accounts
        foreach ($collection as $model) {
            $model->delete();
        }
    }

    /**
     * Get account by accountid
     *
     * @param int $account_id
     *
     * @return \Sectionio\Metrics\Model\AccountFactory
     */
    public function getAccount($account_id)
    {
        $collection = $this->accountFactory->create()->getCollection();
        $collection->addFieldToFilter('account_id', ['eq' => $account_id]);
        return $collection->getFirstItem();
    }

    /**
     * Set active account
     *
     * @param int $account_id
     *
     * @return void
     */
    public function setDefaultAccount($account_id)
    {
        $accountFactory = $this->getAccount($account_id);

        if (! $accountFactory->getData('is_active')) {
            $this->clearDefaultAccount();
            $accountFactory->setData('is_active', '1');
            $accountFactory->save();
        }
    }

    /**
     * Clear default account
     *
     * @return void
     */
    public function clearDefaultAccount()
    {
        /** @var \Sectionio\Metrics\Model\ResourceModel\Account\Collection $collection */
        $collection = $this->accountFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', ['eq' => '1']);
        /** @var \Sectionio\Metrics\Model\AccountFactory $accountFactory */
        $accountFactory = $collection->getFirstItem();

        if ($accountFactory->getData('id')) {
            $accountFactory->setData('is_active', '0');
            $accountFactory->save();
        }
    }

    /**
     * Get application by id
     *
     * @param int $application_id
     *
     * @return \Sectionio\Metrics\Model\ApplicationFactory
     */
    public function getApplication($application_id)
    {
        $collection = $this->applicationFactory->create()->getCollection();
        $collection->addFieldToFilter('application_id', ['eq' => $application_id]);
        return $collection->getFirstItem();
    }

    /**
     * Set active application
     *
     * @param int $application_id
     *
     * @return void
     */
    public function setDefaultApplication($application_id)
    {
        $applicationFactory = $this->getApplication($application_id);

        if (!$applicationFactory->getId()) {
            throw new \InvalidArgumentException('There is no application with the id ' . $application_id);
        }

        if (! $applicationFactory->getData('is_active')) {
            $this->clearDefaultApplication();
            $applicationFactory->setData('is_active', '1');
            $applicationFactory->save();
        }
    }

    /**
     * Clear default application
     *
     * @return void
     */
    public function clearDefaultApplication()
    {
        /** @var \Sectionio\Metrics\Model\ResourceModel\Application\Collection $collection */
        $collection = $this->applicationFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', ['eq' => '1']);
        /** @var \Sectionio\Metrics\Model\ApplicationFactory $applicationFactory */
        $applicationFactory = $collection->getFirstItem();

        if ($applicationFactory->getData('id')) {
            $applicationFactory->setData('is_active', '0');
            $applicationFactory->save();
        }
    }
    
    /**
     * get complete proxy image version for a given environment from aperture.
     *
     * @param int $account_id
     * @param int $application_id
     *
     * @return string
     */
    public function getProxyVersion($account_id, $application_id)
    {
        /**generateApertureUrl takes an associative array */
        $parameters = array("accountId"=>$account_id, "applicationId"=>$application_id, "environmentName"=>"Production");

        /** build the account url */
        $partial_url = $this->generateApertureUrl($parameters);
        $url = $partial_url . "/stack";

        $curl_response = $this->performCurl($url);
        /** return response as an associative array */
        $response = json_decode($curl_response['body_content'], true);

        /** find element with name=varnish, grab the image  */
        $image;
        foreach($response as $proxy){
            if ($proxy['name'] == 'varnish') { 
                $image = $proxy['image'];
            }
        }
        /** returns only the version ie "5.2.1" */
        return explode(":", $image)[1];
    }
    
    /**
     * Takes a full version like "5.2.1" and returns the major release (5)
     * @param string $full_release
     * 
     * @return string
     */
    public function getMajorRelease($full_release)
    {
        return explode(".", $full_release)[0];
    }

    /**
     * Takes in the pageCacheConfig object and a major release and returns the correct vcl
     * @param object $pageCacheConfig
     * @param string $full_release
     * 
     * @return string
     */
    public function getCorrectVCL($pageCacheConfig, $major_release) 
    {
        if ($major_release == "4") {
            return $pageCacheConfig->getVclFile(\Magento\PageCache\Model\Config::VARNISH_4_CONFIGURATION_PATH);
        } else {
            return $pageCacheConfig->getVclFile(\Magento\PageCache\Model\Config::VARNISH_5_CONFIGURATION_PATH);
        }
    }
}
