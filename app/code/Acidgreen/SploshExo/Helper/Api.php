<?php

namespace Acidgreen\SploshExo\Helper;

use Acidgreen\Exo\Helper\Api\Api as ApiHelper;

class Api extends ApiHelper
{
    const CUSTOMTABLE_URI = '/CUSTOMTABLE';

    const TABLE_NAME = 'X_CARTON_SPECS';
    
    const TABLE_PK = 'SEQNO';

    const STAFF_URI = '/staff';

    public function getAllCartonSpecs($params = array()) {
    	$this->logger->debug(__METHOD__);
    	
    	$handler = $this->curlHandler;
    	
    	$queryStringArray = [
            'page' => 1,
            'pagesize' => 100,
    		'table' => self::TABLE_NAME,
    		'pk' => self::TABLE_PK
    	];
        if (!empty($params)) 
            $queryStringArray = array_merge($queryStringArray, $params);
    	
    	$request = $this->apiGetRequest(
    		self::CUSTOMTABLE_URI, 
    		$queryStringArray,
    		[
    			'headers' => [
    				'accept' => ['application/xml']
    			]
    		]
    	);
    	
    	$this->logger->debug(__METHOD__.' :: request dump ::');
    	$this->logger->debug(print_r($request, true));
        sleep(5);
    	$response = $handler($request);
    	
    	$this->logger->debug(__METHOD__.' :: actual response status :: '.$response['status']);
    	
    	// $this->logger->debug(__METHOD__.' :: response dump ::');
    	// $this->logger->debug(print_r($response, true));
    	
        /*
    	// TEMPORARY
    	$response = [
    		'status' => '200'
    	];
         */
    	return $response;
    	
    }

    public function getAllStaff($params = array())
    {
        // API call to staff URI here..
        // return the response object OK? 
        $handler = $this->curlHandler;

        $queryStringArray = [
            'page' => 1,
            'pagesize' => 100,
            'table' => 'STAFF',
            'pk' => 'STAFFNO',
            '$orderby' => 'STAFFNO ASC'
        ];

        if (!empty($params)) {
        	$queryStringArray = array_merge($queryStringArray, $params);
        }

        $request = $this->apiGetRequest(
            self::CUSTOMTABLE_URI, 
            // ['table' => 'STAFF', 'pk' => 'STAFFNO']
            $queryStringArray
        );


        $response = $handler($request);

        if ($response['status'] != '200') {
            $response['error_status'] = $response['status'];
        }

        return $response;
    }
    
    public function sendStaffUpdate($exoStaffData)
    {
    	$handler = $this->curlHandler;
    	
    	$request = $this->apiPutRequest(
    		self::CUSTOMTABLE_URI.'/'.$exoStaffData['id'].'?table=STAFF&pk=STAFFNO', 
    		$exoStaffData);
    	
    	$response = $handler($request);
    	
    	
    	if ($response['status'] != '200') {
    		$response['error_status'] = $response['status'];
    	}
    	
    	return $response;
    }

    /**
     * @inheritDoc
     */
    protected function buildQueryStringArrayActiveCustomers($options = array())
    {
        $queryStringArray = parent::buildQueryStringArrayActiveCustomers($options);

        $b2bWebsiteCodes = $this->configHelper->getScopeConfigWebsite(
            \Acidgreen\SploshExo\Helper\Api\Config::CONFIG_B2B_WEBSITE_CODES
        );
        $salesPersonId = $this->configHelper->getScopeConfigWebsite(
            \Acidgreen\SploshExo\Helper\Customer::CONFIG_DEBTOR_B2C_SALESPERSONID,
            $this->configHelper->getExoCurrentWebsiteId()
        );

        $op = 'eq';
        $isWebsiteB2B = preg_match("/".$this->configHelper->getExoCurrentWebsiteId()."/", $b2bWebsiteCodes);

        if ($isWebsiteB2B)
            $op = 'ne';

        $customFilter = "Active eq true and SalesPersonId ".$op." '".$salesPersonId."'";
        $options['$filter'] = $customFilter;

        $queryStringArray = array_merge($queryStringArray, $options);

        return $queryStringArray;
    }

}
