<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Carrier;

use Magento\Framework\Profiler;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;

/**
 * Class Artificial
 *
 * Describes all carriers created by MageWorx ShippingRules (custom shipping carriers/methods).
 * Includes rates validation and implementation.
 */
class Artificial extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = null;

    /**
     * @var \MageWorx\ShippingRules\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory
     */
    protected $carrierCollectionFactory;

    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Carrier\Collection
     */
    protected $carriersCollection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var array
     */
    protected $loadedCarriers = [];
    /**
     * @var RateRequest
     */
    protected $request;
    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory
     */
    private $rateCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreResolver
     */
    private $storeResolver;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \MageWorx\ShippingRules\Model\CarrierFactory $carrierFactory
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory $carrierCollectionFactory
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory $rateCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreResolver $storeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \MageWorx\ShippingRules\Model\CarrierFactory $carrierFactory,
        \MageWorx\ShippingRules\Model\ResourceModel\Carrier\CollectionFactory $carrierCollectionFactory,
        \MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreResolver $storeResolver,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->rateResultFactory        = $rateResultFactory;
        $this->rateMethodFactory        = $rateMethodFactory;
        $this->carrierFactory           = $carrierFactory;
        $this->carrierCollectionFactory = $carrierCollectionFactory;
        $this->rateCollectionFactory    = $rateCollectionFactory;
        $this->storeManager             = $storeManager;
        $this->storeResolver            = $storeResolver;
        $this->appState                 = $state;
        $this->eventManager             = $eventManager;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Set _code when set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setData('id', $id);
        if ($this->_code === null) {
            $this->_code = $id;
        }

        return $this;
    }

    /**
     * @param RateRequest $request
     * @return bool|array|Result
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        $this->setRequest($request);

        $result = [];
        /** @var \MageWorx\ShippingRules\Model\Carrier $carrier */
        $carrier = $this->findCarrier();
        if (!$carrier) {
            return $result;
        }

        $this->addData($carrier->getData());
        $this->_code = $carrier->getData('carrier_code');

        $storeId = $this->storeResolver->getCurrentStoreId();
        $methods = $carrier->getMethods($storeId);
        if (empty($methods)) {
            return $result;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \MageWorx\ShippingRules\Model\Carrier\Method $methodData */
        foreach ($methods as $methodData) {
            if (!$methodData->getActive()) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->getId());
            $method->setCarrierTitle($carrier->getTitle());
            $method->setMethod($methodData->getData('code'));
            $method->setCost($methodData->getData('cost'));
            $method = $this->applyRates($method, $methodData);

            if ($method) {
                if ($methodData->getAllowFreeShipping() && $request->getFreeShipping() === true) {
                    $method->setPrice('0.00');
                }

                if ($methodData->getDescription()) {
                    $method->setData('method_description', $methodData->getDescription());
                }
                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Find corresponding carrier in the collection
     *
     * @return \MageWorx\ShippingRules\Model\Carrier|null
     */
    protected function findCarrier()
    {
        $carrier = $this->carrierFactory
            ->create()
            ->load($this->getData('id'), 'carrier_code');

        return $carrier;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $method
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function applyRates(
        \Magento\Quote\Model\Quote\Address\RateResult\Method $method,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        $disableMethodWithoutValidRates = $methodData->getDisabledWithoutValidRates();

        $request = $this->getRequest();
        $rates   = $this->getSuitableRatesAccordingRequest($request, $methodData);
        if (empty($rates)) {
            $rates = [];
        }

        $method->setMethodTitle($methodData->getData('title'));
        $method->setPrice($methodData->getData('price'));

        if ($rates) {
            $filteredRates = $this->filterRatesBeforeApply($rates, $request, $methodData);
            /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $validRate */
            foreach ($filteredRates as $validRate) {
                $method = $validRate->applyRateToMethod($method, $request, $methodData);
            }
        } elseif ($disableMethodWithoutValidRates) {
            return null;
        }

        if ($methodData->isNeedToDisplayEstimatedDeliveryTime()) {
            $titleWithDate = $method->getMethodTitle() .
                $methodData->getEstimatedDeliveryTimeMessageFormatted(' (', ')');
            $method->setMethodTitle($titleWithDate);
        }

        return $method;
    }

    /**
     * @return RateRequest
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RateRequest|null $request
     * @return $this
     */
    protected function setRequest(RateRequest $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Find all suitable rates for current method ($methodData argument) according request
     *
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return \Magento\Framework\DataObject[]|\MageWorx\ShippingRules\Api\Data\RateInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     *
     * @note Since your rate doesn't work (not applied/applied incorrect rate) start debugging from here.
     *
     */
    protected function getSuitableRatesAccordingRequest(
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        Profiler::start('load_rates_collection_for_method_' . $methodData->getCode());

        /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $ratesCollection */
        $ratesCollection = $this->rateCollectionFactory->create();

        // General filters should always present
        $ratesCollection->addStoreFilter($request->getStoreId());
        $ratesCollection->addFieldToFilter('active', 1);
        $ratesCollection->addFieldToFilter('method_code', $methodData->getCode());

        // Filters for validate request
        $ratesCollection->addDestinationZipCodeFilters($request->getDestPostcode());
        $ratesCollection->addDestinationCountryFilter($request->getDestCountryId());
        $ratesCollection->addPriceFilter($request->getPackageValue());
        $ratesCollection->addWeightFilter($request->getPackageWeight());
        $ratesCollection->addQtyFilter($request->getPackageQty());

        if ($request->getDestRegionId()) {
            $ratesCollection->addDestinationRegionIdFilter($request->getDestRegionId());
        } elseif ($request->getDestRegionCode()) {
            $ratesCollection->addDestinationRegionFilter($request->getDestRegionCode());
        } else {
            $ratesCollection->addDestinationRegionIdFilter('');
            $ratesCollection->addDestinationRegionFilter('');
        }

        $this->eventManager->dispatch(
            'mageworx_suitable_rates_collection_load_before',
            [
                'rates_collection' => $ratesCollection,
                'method'           => $methodData,
                'request'          => $request,
            ]
        );

        if ($this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $sqlDump = $ratesCollection->getSelectSql(true);
            $this->_logger->log(100, $sqlDump);
        }

        $rates = $ratesCollection->getItems();

        Profiler::stop('load_rates_collection_for_method_' . $methodData->getCode());

        return $rates;
    }

    /**
     * Detect most suitable rate according to the rate's setting
     *
     * @param array $rates
     * @param RateRequest $request
     * @param Method $methodData
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function filterRatesBeforeApply(
        $rates,
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        if (!$rates) {
            return $rates;
        }

        if ($methodData->getMultipleRatesPrice()) {
            $multipleRatesCalculationType = $methodData->getMultipleRatesPrice();
        } else {
            $multipleRatesCalculationType = $this->storeManager
                ->getStore()
                ->getConfig('mageworx_shippingrules/main/multiple_rates_price');
        }

        switch ($multipleRatesCalculationType) {
            case \MageWorx\ShippingRules\Model\Carrier\Method\Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY:
                $resultRate = $this->getRateWithMaxPriority($rates);
                break;
            case \MageWorx\ShippingRules\Model\Carrier\Method\Rate::MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE:
                $resultRate = $this->getRateWithMaxPrice($rates, $request, $methodData);
                break;
            case \MageWorx\ShippingRules\Model\Carrier\Method\Rate::MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE:
                $resultRate = $this->getRateWithMinPrice($rates, $request, $methodData);
                break;
            case \MageWorx\ShippingRules\Model\Carrier\Method\Rate::MULTIPLE_RATES_PRICE_CALCULATION_SUM_UP:
            default:
                return $rates;
        }

        $resultRates = [$resultRate->getId() => $resultRate];

        return $resultRates;
    }

    /**
     * Find rate with max priority in array of rates
     *
     * @param array $rates
     * @return \MageWorx\ShippingRules\Model\Carrier\Method\Rate
     */
    protected function getRateWithMaxPriority($rates)
    {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $currentRate */
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $rate */
        foreach ($rates as $currentRate) {
            if (!isset($rate) || $rate->getPriority() <= $currentRate->getPriority()) {
                $rate = $currentRate;
            }
        }

        return $rate;
    }

    /**
     * Find rate with max price in array of rates
     *
     * @param array $rates
     * @return \MageWorx\ShippingRules\Model\Carrier\Method\Rate
     */
    protected function getRateWithMaxPrice(
        $rates,
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $currentRate */
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $rate */
        $actualRateCalculatedPrice = 0;
        foreach ($rates as $currentRate) {
            $currentRatePrice = $currentRate->getCalculatedPrice($request, $methodData);
            if (!isset($rate) || $actualRateCalculatedPrice <= $currentRatePrice) {
                $rate                      = $currentRate;
                $actualRateCalculatedPrice = $currentRatePrice;
            }
        }

        return $rate;
    }

    /**
     * Find rate with min price in array of rates
     *
     * @param array $rates
     * @return \MageWorx\ShippingRules\Model\Carrier\Method\Rate
     */
    protected function getRateWithMinPrice(
        $rates,
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $currentRate */
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $rate */
        $actualRateCalculatedPrice = 0;
        foreach ($rates as $currentRate) {
            $currentRatePrice = $currentRate->getCalculatedPrice($request, $methodData);
            if (!isset($rate) || $actualRateCalculatedPrice >= $currentRatePrice) {
                $rate                      = $currentRate;
                $actualRateCalculatedPrice = $currentRatePrice;
            }
        }

        return $rate;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        $carrier = $this->findCarrier();
        if (!$carrier) {
            return [];
        }

        /** @var \MageWorx\ShippingRules\Api\Data\MethodInterface[] $methods */
        $methods        = $carrier->getMethods();
        $allowedMethods = [];
        foreach ($methods as $method) {
            $allowedMethods[$method->getCode()] = $method->getTitle();
        }

        return $allowedMethods;
    }

    /**
     * Get all data of the carrier specified by code (carrier_code)
     * It's possible to get the specified parameter ($param) of the carrier
     *
     * @param string $code
     * @param null $param
     * @return mixed|null
     */
    protected function getSpecificCarrierData($code, $param = null)
    {
        $item = $this->carriersCollection->getItemByColumnValue('carrier_code', $code);
        if (!$item) {
            return null;
        }

        if (!$param) {
            return $item->getData();
        }

        return $item->getData($param);
    }
}
