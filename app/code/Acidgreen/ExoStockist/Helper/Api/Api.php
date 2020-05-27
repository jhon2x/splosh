<?php

namespace Acidgreen\ExoStockist\Helper\Api;

use Acidgreen\Exo\Helper\Api\Config as ConfigHelper;
use GuzzleHttp\Ring\Client\CurlHandler;
use GuzzleHttp\Ring\Client\StreamHandler;
use GuzzleHttp\Ring\Core;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Exception\LocalizedException;

use Magento\Store\Model\StoreManagerInterface;


class Api extends \Acidgreen\Exo\Helper\Api\Api
{
    const MYOB_STOCKIST_URI = '/geolocationtemplate';
    const MYOB_STOCKIST_SEARCH_TEMPLATE_ID_AU = 22;
    const MYOB_STOCKIST_SEARCH_TEMPLATE_ID_NZ = 20;
    /**
     * SPL-360 - Deprecated, though changed to 35 for clarification
     */
    const MYOB_SEARCH_RADIUS = 35; //in KM

    const CONFIG_MYOB_STOCKIST_SEARCH_TEMPLATE_ID = 'acidgreen_exo_apisettings/exostockist_settings/myob_stockist_search_template_id';
    const CONFIG_MYOB_SEARCH_RADIUS = 'acidgreen_exo_apisettings/exostockist_settings/myob_search_radius';

    const GEOCODE_API_URI = 'https://maps.google.com/maps/api/geocode/json?key=AIzaSyAY0iGKPEESSddy1glT5w1-bBaX4Qus5ZI';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * __construct
     *
     * @param ConfigHelper $configHelper
     * @param Core $ringCore
     * @param Logger $logger
     */
    public function __construct(
    	ConfigHelper $configHelper,
        Core $ringCore,
        Logger $logger,
        StoreManagerInterface $storeManager
    ){

    	$this->configHelper = $configHelper;
        $this->curlHandler = new CurlHandler();
        $this->ringCore     = $ringCore;
        $this->logger       = $logger;

        $this->storeManager = $storeManager;
    }

    /**
     * Search MYOB Stockists
     *
     * @return Array $data
     */
    public function getExoStockists($postcode, $stockcode)
    {
        $this->logger->debug('getExoStockists');

        /**
         * SPL-360 - Get radius from system config
         */
        $currentWebsite = $this->storeManager->getWebsite();
        $myobSearchRadius = $this->configHelper->getScopeConfigWebsite(self::CONFIG_MYOB_SEARCH_RADIUS, $currentWebsite->getCode());
        // @todo: retrieve template ID from the system config when the need arises

        $geocodeData = $this->geocode($postcode);

        if ($geocodeData) {

            $handler = $this->curlHandler;

            $queryStringArray = [
                        'STOCKCODE'      => $stockcode,
                        'currentlat'  => $geocodeData[0],
                        'currentlong'   => $geocodeData[1],
                        'radius'   => $myobSearchRadius,
                        'days'   => '90'
                    ];

            $this->logger->debug('SPL-360 :: queryStringArray :: '.print_r($queryStringArray, true));

            $templateID = self::MYOB_STOCKIST_SEARCH_TEMPLATE_ID_AU;
            if (preg_match('/nz/', $this->configHelper->getExoCurrentWebsiteId())) {
                $templateID = self::MYOB_STOCKIST_SEARCH_TEMPLATE_ID_NZ;
            }

            $requestUrl = self::MYOB_STOCKIST_URI.'/'.$templateID;

            $request = $this->apiGetRequest($requestUrl, $queryStringArray);

            $response = $handler($request);

            $this->logger->debug(print_r($response, true));

            $responseBody   = \GuzzleHttp\Ring\Core::body($response);

            $exoStockistData = json_decode($responseBody,true);
            $exoStockistData['parsedLat'] = $geocodeData[0];
            $exoStockistData['parsedLong'] = $geocodeData[1];

            return $exoStockistData;

        } else {
          $this->logger->debug('No Geocode Location returned');
          return false;
        }

    }

    /**
     * Geocode Postcode via Google maps api
     *
     * @return Array $data
     */
    function geocode($postcode){

        /**
         * SPL-348 - Get correct country to search for...
         */
        $country = 'Australia';
        $countryCodeComponent = 'AU';
        if (preg_match('/nz/', $this->configHelper->getExoCurrentWebsiteId())) {
            $country = 'New Zealand';
            $countryCodeComponent = 'NZ';
        }

        $address = urlencode($country.' '.$postcode);
        $componentsQueryString = 'components=country:'.$countryCodeComponent;

        $url = self::GEOCODE_API_URI.'&address='.$address.'&'.$componentsQueryString;
        $this->logger->debug('SPL-348 :: $url used :: '.$url);
        $resp_json = file_get_contents($url);
        $resp = json_decode($resp_json, true);

        if($resp['status']=='OK'){

            $lati = $resp['results'][0]['geometry']['location']['lat'];
            $longi = $resp['results'][0]['geometry']['location']['lng'];
            $formatted_address = $resp['results'][0]['formatted_address'];

            if($lati && $longi && $formatted_address){

                $data_arr = array();

                array_push(
                    $data_arr,
                    $lati,
                    $longi,
                    $formatted_address
                );

                return $data_arr;

            } else {
                return false;
            }

        } else {
            return false;
        }
    }
}
