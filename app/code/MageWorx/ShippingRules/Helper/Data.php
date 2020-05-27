<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\View\ConfigInterface as ViewConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Store\Model\ScopeInterface;
use MageWorx\ShippingRules\Serializer\SerializeJson;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const MEDIA_TYPE_CONFIG_NODE        = 'images';
    const IMAGE_TYPE_THUMBNAIL          = 'thumbnail_image';
    const IMAGE_TYPE_FORM_PREVIEW       = 'preview_in_form';
    const IMAGE_TYPE_FRONTEND_PREVIEW   = 'preview_frontend';
    const BASE_MEDIA_PATH_EXTENDED_ZONE = 'mageworx/shippingrules/extended_zone';

    const XML_PATH_POPUP_ENABLED             = 'mageworx_shippingrules/popup/enabled';
    const XML_PATH_POPUP_ONLY_ADDRESS_FIELDS = 'mageworx_shippingrules/popup/only_address';

    const XML_PATH_VALIDATION_POSTCODE_EXCESSIVE_VALID  =
        'mageworx_shippingrules/validation/postcode_validation_excessive_valid';
    const XML_PATH_ADVANCED_POSTCODE_VALIDATION_ENABLED =
        'mageworx_shippingrules/validation/advanced_postcode_validation_enabled';
    const XML_PATH_EXTENDED_COUNTRY_SELECT_ENABLED      =
        'mageworx_shippingrules/validation/extended_country_select_enabled';
    const XML_PATH_SINGLE_ADDRESS_ZONE_MODE             =
        'mageworx_shippingrules/validation/single_address_zone_mode';

    const XML_PATH_UK_POST_CONDITIONS_ENABLED = 'mageworx_shippingrules/validation/uk_postcode_conditions';
    const XML_PATH_SHIPPING_METHODS_TITLES    = 'mageworx_shippingrules/shipping_methods/renaming';
    const XML_PATH_MAX_COUNTRIES_COUNT        = 'mageworx_shippingrules/shipping_methods/rates/max_countries';
    const XML_PATH_MAX_REGIONS_COUNT          = 'mageworx_shippingrules/shipping_methods/rates/max_regions';

    const XML_PATH_DISPLAY_CHEAPEST_RATE_AT_TOP_ENABLED =
        'mageworx_shippingrules/shipping_methods/display_cheapest_rate_top';
    const XML_PATH_RESOLVE_PARAMETERS_FROM_API_REQUEST  =
        'mageworx_shippingrules/developer/resolve_parameters_from_api_request';
    const XML_PATH_IMPORT_EXPORT_USE_ID                 =
        'mageworx_shippingrules/import/use_id';

    const XML_PATH_ALLOWED_COUNTRIES    = 'general/country/allow';
    const XML_PATH_LOGGER_ENABLED       = 'mageworx_shippingrules/developer/logger_enabled';
    const XML_PATH_SHIPPING_PER_PRODUCT = 'mageworx_shippingrules/main/shipping_per_product';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $EuCountriesList = [];

    /**
     * @var array
     */
    protected $defaultEuCountriesList = [
        'BE',
        'BG',
        'CZ',
        'DK',
        'DE',
        'EE',
        'IE',
        'EL',
        'ES',
        'FR',
        'HR',
        'IT',
        'CY',
        'LV',
        'LT',
        'LU',
        'HU',
        'MT',
        'NL',
        'AT',
        'PL',
        'PT',
        'RO',
        'SI',
        'SK',
        'FI',
        'SE',
        'UK'
    ];

    /**
     * Array where parsed uk postcodes stored
     *
     * @var []
     */
    protected $ukPostCodesParsed;

    /**
     * @var ViewConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var []
     */
    protected $methodTitles;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var SerializeJson
     */
    protected $serializer;

    /**
     * List of allowed countries for this website (current)
     *
     * @var array
     */
    protected $allowedCountries = [];

    /**
     * @var array
     */
    protected $codesByCountry = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \MageWorx\ShippingRules\Serializer\SerializeJson $serializer
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ViewConfigInterface $viewConfig,
        Filesystem $filesystem,
        ImageFactory $imageFactory,
        Random $mathRandom,
        SerializeJson $serializer
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->viewConfig   = $viewConfig;
        $this->filesystem   = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->mathRandom   = $mathRandom;
        $this->serializer   = $serializer;
    }

    /**
     * Check is popup (frontend) enabled
     *
     * @param null $storeId
     *
     * @return boolean
     */
    public function isEnabledPopup($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_POPUP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Setting: show only address field without zones selection
     *
     * @param null $storeId
     * @return bool
     */
    public function isOnlyAddressFieldsShouldBeShown($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_POPUP_ONLY_ADDRESS_FIELDS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is invalid postcode when there is excessive data entered by an user
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function getPostcodeExcessiveValid($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_VALIDATION_POSTCODE_EXCESSIVE_VALID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is advanced (primarily for the UK) validation enabled
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isAdvancedPostCodeValidationEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADVANCED_POSTCODE_VALIDATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is advanced conditions enabled for the UK post code validation  (parts)
     *
     * @param null $storeId
     * @return bool
     */
    public function isUKSpecificPostcodeConditionsEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_UK_POST_CONDITIONS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is need to display chipest shipping rate at top
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function displayCheapestRateAtTop($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_CHEAPEST_RATE_AT_TOP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Resolve or not the address parameters from the API request during rates collection
     *
     * @see \MageWorx\ShippingRules\Model\Rule\Condition\Address::validate()
     * @see \MageWorx\ShippingRules\Model\Rule\Condition\Address::resolveParametersFromApiRequest()
     *
     * @param null $storeId
     * @return bool
     */
    public function isNeedToResolveParametersFromApiRequest($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RESOLVE_PARAMETERS_FROM_API_REQUEST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is single zone mode enabled for the address validation
     *
     * @param null $storeId
     * @return bool
     */
    public function isSingleAddressZoneMode($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SINGLE_ADDRESS_ZONE_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is extended country select enabled (used in the address validation
     *
     * @param null $storeId
     * @return bool
     */
    public function isExtendedCountrySelectEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXTENDED_COUNTRY_SELECT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is ids field should be used to detect entities relation (old -> new)
     *
     * @param null $storeId
     * @return bool
     */
    public function isIdsUsedDuringImport($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IMPORT_EXPORT_USE_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is shipping rules logger enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isLoggerEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_LOGGER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check is country in the list of allowed on the website
     *
     * @param string $code
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     */
    public function isCountryAllowed(
        $code,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $scopeCode = null
    ) {
        $allowedCountries = $this->getAllowedCountries($scope, $scopeCode);
        if (in_array($code, $allowedCountries)) {
            return true;
        }

        return false;
    }

    /**
     * Get list of allowed countries for the current website
     *
     * @param string $scope
     * @param null $scopeCode
     * @return array
     */
    public function getAllowedCountries(
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $scopeCode = null
    ) {
        if (!empty($this->allowedCountries)) {
            return $this->allowedCountries;
        }

        $this->allowedCountries = $this->getCountriesFromConfig($scope, $scopeCode);

        return $this->allowedCountries;
    }

    /**
     * Takes countries from Countries Config data
     *
     * @param string $scope
     * @param int $scopeCode
     *
     * @return array
     */
    public function getCountriesFromConfig($scope, $scopeCode)
    {
        return explode(
            ',',
            (string)$this->scopeConfig->getValue(
                self::XML_PATH_ALLOWED_COUNTRIES,
                $scope,
                $scopeCode
            )
        );
    }

    /**
     * Get maximum count of countries displayed in the rates listing
     *
     * @param null $storeId
     * @return int
     */
    public function getMaxCountriesCount($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_COUNTRIES_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get maximum count of regions displayed in the rates listing
     *
     * @param null $storeId
     * @return int
     */
    public function getMaxRegionsCount($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_REGIONS_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is shipping per product enabled.
     * Shipping per product adds a restriction to the shipping methods using corresponding product attribute.
     *
     * @param null $storeId
     * @return bool
     */
    public function getShippingPerProduct($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHIPPING_PER_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get specific method title
     *
     * @param string $code
     * @param null $storeId
     *
     * @return null
     * @throws \Exception
     */
    public function getMethodTitle($code, $storeId = null)
    {
        $methodTitles = $this->getMethodsTitles($storeId);
        if (empty($methodTitles[$code])) {
            return null;
        }

        return $methodTitles[$code];
    }

    /**
     * Get renamed method titles (all)
     *
     * @param null $storeId
     *
     * @param bool $raw
     *
     * @return array|string|null
     * @throws \Exception
     */
    public function getMethodsTitles($storeId = null, $raw = false)
    {
        if (!empty($this->methodTitles) && !$raw) {
            return $this->methodTitles;
        }

        $value = $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_METHODS_TITLES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($raw) {
            return $value;
        }

        if (!$value) {
            $this->methodTitles = [];

            return $this->methodTitles;
        }

        $this->methodTitles = $this->unserializeValue($value);

        return $this->methodTitles;
    }

    /**
     * Create a value from a storable representation
     *
     * @param int|float|string $value
     * @return array
     * @throws \Exception
     */
    public function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return $this->serializer->unserialize($value);
        } else {
            return [];
        }
    }

    /**
     * Returns array of the EU country codes
     *
     * @return array
     */
    public function getEuCountries()
    {
        if (!empty($this->EuCountriesList)) {
            return $this->EuCountriesList;
        }

        $euCountries = $this->scopeConfig->getValue('general/country/eu_countries');
        if (!$euCountries) {
            $this->EuCountriesList = $this->defaultEuCountriesList;

            return $this->EuCountriesList;
        }

        $this->EuCountriesList = explode(',', $euCountries);

        return $this->EuCountriesList;
    }

    /**
     * Returns array of selected countries for the specified region
     *
     * @param string|int $threeDigitCode
     *
     * @return array
     */
    public function resolveCountriesByDigitCode($threeDigitCode)
    {
        $countries      = $this->scopeConfig->getValue('mageworx_shippingrules/countries/country_' . $threeDigitCode);
        $countriesArray = explode(',', $countries);

        return $countriesArray;
    }

    /**
     * Get all available digit-codes (geo region codes) for the country
     *
     * @param string $countryCode
     * @return array
     */
    public function getDigitCodesForCountry($countryCode)
    {
        if (!empty($this->codesByCountry[$countryCode])) {
            return $this->codesByCountry[$countryCode];
        }

        $codes             = [];
        $countriesByRegion = $this->scopeConfig->getValue('mageworx_shippingrules/countries');
        foreach ($countriesByRegion as $vagueCode => $countryList) {
            $code             = str_ireplace('country_', '', $vagueCode);
            $countryListArray = explode(',', $countryList);
            if (in_array($countryCode, $countryListArray)) {
                $codes[] = $code;
            }
        }

        $this->codesByCountry[$countryCode] = $codes;

        return $this->codesByCountry[$countryCode];
    }

    /**
     * @param string $file
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $this->getBaseMediaPath();
    }

    /**
     * Filesystem directory path of option value images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return static::BASE_MEDIA_PATH_EXTENDED_ZONE;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Get image url for specified type, width or height
     *
     * @param string $path
     * @param null $type
     * @param int $height
     * @param int $width
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($path, $type = null, $height = 300, $width = 300)
    {
        if (!$path) {
            return '';
        }

        if ($type !== null) {
            $attributes = $this->getAttributesByType($type);
            $height     = !empty($attributes['height']) ? $attributes['height'] : $height;
            $width      = !empty($attributes['width']) ? $attributes['width'] : $width;
        }

        $filePath      = $this->getMediaPath($path);
        $pathArray     = explode('/', $filePath);
        $fileName      = array_pop($pathArray);
        $directoryPath = implode('/', $pathArray);
        $imagePath     = $directoryPath . '/' . $width . 'x' . $height . '/';

        $mediaDirectory   = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imgAbsolutePath  = $mediaDirectory->getAbsolutePath($imagePath);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($filePath);

        $imgFilePath = $imgAbsolutePath . $fileName;
        if (!file_exists($imgFilePath)) {
            $this->createImageFile($fileAbsolutePath, $imgAbsolutePath, $fileName, $width, $height);
        }

        return $this->getUrl($imagePath . $fileName);
    }

    /**
     * Get image attributes by type
     *
     * @param string $type
     *
     * @return array
     */
    private function getAttributesByType($type)
    {
        $data = [];
        switch ($type) {
            case static::IMAGE_TYPE_THUMBNAIL:
                $data['width']  = 75;
                $data['height'] = 75;
                break;
            case static::IMAGE_TYPE_FORM_PREVIEW:
                $data['width']  = 116;
                $data['height'] = 148;
                break;
            case static::IMAGE_TYPE_FRONTEND_PREVIEW:
                $data['width']  = 150;
                $data['height'] = 150;
                break;
            default:
                $data['width']  = 300;
                $data['height'] = 300;
                break;
        }

        return $data;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->prepareFile($file);
    }

    /**
     * Create image based on size
     *
     * @param string $origFilePath
     * @param string $imagePath
     * @param string $newFileName
     * @param string|int|float $width
     * @param string|int|float $height
     *
     */
    private function createImageFile($origFilePath, $imagePath, $newFileName, $width, $height)
    {
        try {
            $image = $this->imageFactory->create($origFilePath);
            $image->keepAspectRatio(true);
            $image->keepFrame(true);
            $image->keepTransparency(true);
            $image->constrainOnly(false);
            $image->backgroundColor([255, 255, 255]);
            $image->quality(100);
            $image->resize($width, $height);
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);
            $image->save($imagePath, $newFileName);
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    /**
     * @param string $file
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl($file)
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($this->prepareFile($file), '/');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * Get file size in bytes. Used in uploader element (form)
     *
     * @param string $image
     *
     * @return int
     */
    public function getImageOrigSize($image)
    {
        $fullPathToImage  = $this->getMediaPath($image);
        $mediaDirectory   = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($fullPathToImage);
        if (file_exists($fileAbsolutePath)) {
            $fileSize = @filesize($fileAbsolutePath);
        } else {
            return 0;
        }

        return $fileSize;
    }

    /**
     * Parse float from the money string
     * Thanks to the author: @mcuadros
     *
     * @link https://stackoverflow.com/questions/5139793/php-unformat-money
     * @link https://github.com/mcuadros/currency-detector
     *
     * @param string $money
     *
     * @return float
     */
    public function getAmount($money)
    {
        $cleanString             = preg_replace('/([^0-9\.,])/i', '', $money);
        $cleanStringWithDotsOnly = preg_replace('/([,])/i', '.', $cleanString);
        $parts                   = explode('.', $cleanStringWithDotsOnly);
        if (count($parts) > 1) {
            $rightPart    = array_pop($parts);
            $leftPart     = !empty($parts) ? implode('', $parts) : '0';
            $resultString = $leftPart . '.' . $rightPart;
        } else {
            $resultString = $cleanStringWithDotsOnly;
        }

        return (float)$resultString;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param int|float|string|array $value
     * @return string
     * @throws \Exception
     */
    public function serializeValue($value)
    {
        if (is_array($value)) {
            $data = [];
            foreach ($value as $methodId => $title) {
                if (!array_key_exists($methodId, $data)) {
                    $data[$methodId] = $title;
                }
            }

            return $this->serializer->serialize($data);
        } else {
            return '';
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param string|array $value
     * @return bool
     */
    public function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('methods_id', $row)
                || !array_key_exists('title', $row)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    public function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $methodId => $title) {
            $resultId          = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = ['methods_id' => $methodId, 'title' => $title];
        }

        return $result;
    }

    /**
     * Decode value from used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    public function decodeArrayFieldValue(array $value)
    {
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('methods_id', $row)
                || !array_key_exists('title', $row)
            ) {
                continue;
            }
            $methodId          = $row['methods_id'];
            $title             = $row['title'];
            $result[$methodId] = $title;
        }

        return $result;
    }

    /**
     * Parse UK postcode
     * Returns it by parts:
     *  'area'
     *  'district'
     *  'sector'
     *  'outcode'
     *  'incode'
     *  'formatted'
     *
     * @param string $postcode
     * @return array
     */
    public function parseUkPostCode($postcode)
    {
        if (!empty($this->ukPostCodesParsed[$postcode])) {
            return $this->ukPostCodesParsed[$postcode];
        }

        if (!$postcode) {
            return [];
        }

        // Get in-code and out-code
        if (mb_stripos($postcode, ' ') !== false) {
            $twoParts = explode(' ', $postcode);
            $outcode  = !empty($twoParts[0]) ? $twoParts[0] : null;
            $incode   = !empty($twoParts[1]) ? $twoParts[1] : null;
        } else {
            preg_match(
                '/^([A-Za-z]{1,2}([\d]{2}|[\d]{1}[A-Za-z]{1}|[\d]{1}){1})[\s]?([\d]{1}[A-Za-z]{2})?$/',
                $postcode,
                $match
            );
            $outcode = !empty($match[1]) ? $match[1] : '';
            $incode  = !empty($match[3]) ? $match[3] : '';
        }

        // Get other parts
        $chunksOne = $this->explodeStringByAlphaDigits(
            $outcode
        ); // [A-Za-z]{1,2}([\d]{2}|[\d]{1}[A-Za-z]{1}){1}[\s]?[\d]{1}[A-Za-z]{2}
        $chunksTwo = $this->explodeStringByAlphaDigits($incode);
        $chunks    = array_merge($chunksOne, $chunksTwo);

        $area     = !empty($chunks[0]) ? $chunks[0] : null;
        $district = !empty($outcode) && !empty($area) ? str_ireplace($area, '', $outcode) : null;
        $sector   = !empty($incode) ? mb_substr($incode, 0, 1) : null;
        $unit     = !empty($incode) && !empty($sector) ? str_ireplace($sector, '', $incode) : null;

        $this->ukPostCodesParsed[$postcode] = [
            'uk_area'      => $area,
            'uk_district'  => $district,
            'uk_sector'    => $sector,
            'uk_unit'      => $unit,
            'uk_outcode'   => $outcode,
            'uk_incode'    => $incode,
            'uk_full_code' => $postcode
        ];

        foreach ($this->ukPostCodesParsed[$postcode] as &$part) {
            $part = mb_strtoupper($part);
        }

        return $this->ukPostCodesParsed[$postcode];
    }

    /**
     * Explode string by digits and letters part
     *
     * @param string $string
     *
     * @return array
     */
    public function explodeStringByAlphaDigits($string)
    {
        if (preg_match_all('~[a-zA-Z]+|\d+|[^\da-zA-Z]+~', $string, $chunks)) {
            return $chunks[0];
        }

        return [];
    }
}
