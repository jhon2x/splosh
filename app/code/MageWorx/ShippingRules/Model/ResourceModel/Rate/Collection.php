<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel\Rate;

use MageWorx\ShippingRules\Api\Data\RateInterface;
use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use Magento\Framework\DB\Select;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZip;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipNL;
use MageWorx\ShippingRules\Model\ZipCode\AlphaNumericZipUK;
use MageWorx\ShippingRules\Model\ZipCode\NumericZip;
use MageWorx\ShippingRules\Model\ZipCodeManager;
use Zend_Db_Select_Exception;

/**
 * Class Collection
 *
 * @method \MageWorx\ShippingRules\Model\ResourceModel\Rate getResource()
 */
class Collection extends \MageWorx\ShippingRules\Model\ResourceModel\AbstractCollection
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_shippingrules_rates_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'rates_collection';

    /**
     * Store associated with rate entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table'    => CarrierModel::RATE_TABLE_NAME . '_store',
            'main_table_id_field'   => 'rate_id',
            'linked_table_id_field' => 'entity_id',
            'entity_id_field'       => 'store_id',
        ]
    ];

    /**
     * @var bool
     */
    private $countryTableJoined = false;

    /**
     * @var bool
     */
    private $regionTableJoined = false;

    /**
     * @var bool
     */
    private $regionIdTableJoined = false;

    /**
     * @var bool
     */
    private $zipsTablesJoined = false;

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MageWorx\ShippingRules\Model\Carrier\Method\Rate',
            'MageWorx\ShippingRules\Model\ResourceModel\Rate'
        );
        $this->_map['fields']['rate_id']                     = 'main_table.rate_id';
        $this->_map['fields']['title']                       = 'main_table.title';
        $this->_map['fields']['price']                       = 'main_table.price';
        $this->_map['fields']['created_at']                  = 'main_table.created_at';
        $this->_map['fields']['updated_at']                  = 'main_table.updated_at';
        $this->_map['fields']['active']                      = 'main_table.active';
        $this->_map['fields']['estimated_delivery_time_min'] = 'main_table.estimated_delivery_time_min';
        $this->_map['fields']['estimated_delivery_time_max'] = 'main_table.estimated_delivery_time_max';
        $this->_setIdFieldName('rate_id');
    }

    /**
     * Filter rates collection according destination zip code
     *
     * @param string|int $destZipCode
     * @return $this
     * @throws Zend_Db_Select_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addDestinationZipCodeFilters($destZipCode)
    {
        $destZipCode         = preg_replace('/-.+/ui', '', $destZipCode);
        $alphanumericZipCode = preg_match('/[^\d]/ui', $destZipCode);
        if ($alphanumericZipCode) {
            $zip = (string)$destZipCode;
        } else {
            $zip = (int)$destZipCode;
        }

        // Case when zip code is not set - valid only rates without zip validation enabled
        if (empty($zip)) {
            $this->getSelect()->where('(`main_table`.`zip_validation_mode` = 0)');

            return $this;
        }

        // Detect incoming zip format and join zip-tables according format
        $zipFormat = $this->getResource()->detectZipFormat($zip);
        $this->joinZipsTables($zipFormat);

        $zipDiapasonTableAlias = $this->getResource()->getZipDiapasonTableAliasByFormat($zipFormat);

        $select = $this->getSelect();
        $joins  = $select->getPart('from');
        if (stripos($joins[$zipDiapasonTableAlias]['joinCondition'], '`inverted` = 0') === false) {
            $joins[$zipDiapasonTableAlias]['joinCondition'] .= ' AND `inverted` = 0';
            $select->setPart('from', $joins);
        }

        $plainZipTable = $this->getTable(RateInterface::RATE_ZIPS_TABLE_NAME);

        $excludedZipSelect = $this->getConnection()->select()->from(
            $plainZipTable,
            ['rate_id']
        )->where(
            'zip = ?',
            $zip
        )->where(
            'inverted = ?',
            1
        );

        $rateWithInvertedZipsSelect = $this->getConnection()->select()->from(
            $plainZipTable,
            ['rate_id']
        )->where(
            'inverted = ?',
            1
        );

        $zipFormatter         = $this->getResource()->detectZipFormatter($zip);
        $zipDiapasonCondition = $zipFormatter->createConditionByZip($this, $zip);

        $whereSql = sprintf(
            '(
                    (`main_table`.`zip_validation_mode` = 0)
                    OR
                    (
                        `main_table`.`zip_validation_mode` = 1 
                        AND
                        (
                            (`zip` = ? AND `zt`.`inverted` = 0) OR
                            (
                                `main_table`.`rate_id` NOT IN (%1$s)
                                AND
                                `main_table`.`rate_id` IN (%3$s)
                            )
                        )
                    )
                    OR
                    (
                        %2$s
                    )
                )',
            $excludedZipSelect,
            $zipDiapasonCondition,
            $rateWithInvertedZipsSelect
        );

        $this->getSelect()->where($whereSql, $zip);

        return $this;
    }

    /**
     * Join 2 tables where zip codes validation stored
     *
     * @param null $format
     * @return $this
     */
    public function joinZipsTables($format = null)
    {
        if ($this->zipsTablesJoined) {
            return $this;
        }

        if ($format === null || $format === ZipCodeManager::NUMERIC_FORMAT) {
            $alias = $this->getResource()->getZipDiapasonTableAliasByFormat(ZipCodeManager::NUMERIC_FORMAT);
            $this->joinLeft(
                [
                    $alias => $this->getTable(NumericZip::TABLE_NAME)
                ],
                'main_table.rate_id = ' . $alias . '.rate_id',
                []
            );
        }

        if ($format === null || $format === ZipCodeManager::ALPHANUMERIC_FORMAT) {
            $alias = $this->getResource()->getZipDiapasonTableAliasByFormat(ZipCodeManager::ALPHANUMERIC_FORMAT);
            $this->joinLeft(
                [
                    $alias => $this->getTable(AlphaNumericZip::TABLE_NAME)
                ],
                'main_table.rate_id = ' . $alias . '.rate_id',
                []
            );
        }

        if ($format === null || $format === ZipCodeManager::ALPHANUMERIC_FORMAT_UK) {
            $alias = $this->getResource()->getZipDiapasonTableAliasByFormat(ZipCodeManager::ALPHANUMERIC_FORMAT_UK);
            $this->joinLeft(
                [
                    $alias => $this->getTable(AlphaNumericZipUK::TABLE_NAME)
                ],
                'main_table.rate_id = ' . $alias . '.rate_id',
                []
            );
        }

        if ($format === null || $format === ZipCodeManager::ALPHANUMERIC_FORMAT_NL) {
            $alias = $this->getResource()->getZipDiapasonTableAliasByFormat(ZipCodeManager::ALPHANUMERIC_FORMAT_NL);
            $this->joinLeft(
                [
                    $alias => $this->getTable(AlphaNumericZipNL::TABLE_NAME)
                ],
                'main_table.rate_id = ' . $alias . '.rate_id',
                []
            );
        }

        $this->joinLeft(
            [
                'zt' => $this->getTable(RateInterface::RATE_ZIPS_TABLE_NAME)
            ],
            'main_table.rate_id = zt.rate_id',
            []
        );

        $this->zipsTablesJoined = true;

        return $this;
    }

    /**
     * Filter rates collection by country id (code) which stored in the separate table:
     * mageworx_shippingrules_rates_country
     *
     * @param string $countryId
     * @return $this
     */
    public function addDestinationCountryFilter($countryId)
    {
        $this->joinCountryTable();
        $countriesByRegion = $this->helper->getDigitCodesForCountry($countryId);
        $this->addFieldToFilter(
            'country_code',
            [
                ['eq' => $countryId],
                ['null' => true],
                ['in' => $countriesByRegion]
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function joinCountryTable()
    {
        if ($this->countryTableJoined) {
            return $this;
        }

        $this->joinLeft(
            [
                'rct' => $this->getTable(RateInterface::RATE_COUNTRY_TABLE_NAME)
            ],
            'main_table.rate_id = rct.rate_id',
            []
        );

        $this->countryTableJoined = true;

        return $this;
    }

    /**
     * Filter rates collection by region which stored in the separate tables:
     * mageworx_shippingrules_rates_region
     * mageworx_shippingrules_rates_region_id
     *
     * @param string $destinationRegion
     * @return $this
     */
    public function addDestinationRegionFilter($destinationRegion)
    {
        $this->joinRegionTable();

        $this->addFieldToFilter(
            'rrt.region',
            [
                ['eq' => $destinationRegion],
                ['null' => true],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function joinRegionTable()
    {
        if ($this->regionTableJoined) {
            return $this;
        }

        $this->joinLeft(
            [
                'rrt' => $this->getTable(RateInterface::RATE_REGION_TABLE_NAME)
            ],
            'main_table.rate_id = rrt.rate_id',
            []
        );

        $this->regionTableJoined = true;

        return $this;
    }

    /**
     * Filter rates collection by region id which stored in the separate table:
     * mageworx_shippingrules_rates_region_id
     *
     * @param int|string $destinationRegionId
     * @return $this
     */
    public function addDestinationRegionIdFilter($destinationRegionId)
    {
        $this->joinRegionIdTable();

        $this->addFieldToFilter(
            'rridt.region_id',
            [
                ['eq' => $destinationRegionId],
                ['null' => true],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function joinRegionIdTable()
    {
        if ($this->regionIdTableJoined) {
            return $this;
        }

        $this->joinLeft(
            [
                'rridt' => $this->getTable(RateInterface::RATE_REGION_ID_TABLE_NAME)
            ],
            'main_table.rate_id = rridt.rate_id',
            []
        );

        $this->regionIdTableJoined = true;

        return $this;
    }

    /**
     * Filter rates collection by price condition (from-to)
     *
     * @param float|string $price
     * @return $this
     */
    public function addPriceFilter($price)
    {
        // Rate's "price from" must be less than actual price from request or NULL
        $this->addFieldToFilter(
            'price_from',
            [
                ['lteq' => $price],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        // Rate's "price to" must be greater than actual price from request or NULL
        $this->addFieldToFilter(
            'price_to',
            [
                ['gteq' => $price],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        return $this;
    }

    /**
     * Filter rates collection by weight condition (from-to)
     *
     * @param float|string $weight
     * @return $this
     */
    public function addWeightFilter($weight)
    {
        // Rate's "weight from" must be less than actual weight from request or NULL
        $this->addFieldToFilter(
            'weight_from',
            [
                ['lteq' => $weight],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        // Rate's "weight to" must be greater than actual weight from request or NULL
        $this->addFieldToFilter(
            'weight_to',
            [
                ['gteq' => $weight],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        return $this;
    }

    /**
     * Filter rates collection by qty condition (from-to)
     *
     * @param float|string $qty
     * @return $this
     */
    public function addQtyFilter($qty)
    {
        // Rate's "qty from" must be less than actual qty from request or NULL
        $this->addFieldToFilter(
            'qty_from',
            [
                ['lteq' => $qty],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        // Rate's "qty to" must be greater than actual qty from request or NULL
        $this->addFieldToFilter(
            'qty_to',
            [
                ['gteq' => $qty],
                ['null' => true],
                ['eq' => 0]
            ]
        );

        return $this;
    }

    /**
     * Redeclare before load method for adding sort order
     *
     * @return \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->addOrder('priority', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Add carrier to the collection
     */
    protected function _renderFiltersBefore()
    {
        $this->_eventManager->dispatch('rates_collection_render_filters_before', ['collection' => $this]);

        parent::_renderFiltersBefore();
    }

    /**
     * Convert items array to array for select options
     *
     * return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'rate_id', $labelField = 'title', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * Add country id column to the main data
     *
     * @return $this
     */
    protected function addCountryIdColumn()
    {
        $this->joinCountryTable();
        $select = $this->getSelect();
        $select->columns(
            ['country_id' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `rct`.`country_code` SEPARATOR \',\')')]
        );
        $this->groupByMainField();

        return $this;
    }

    /**
     * Group select by main field (one time)
     *
     * @return $this
     */
    protected function groupByMainField()
    {
        try {
            $group          = $this->getSelect()->getPart(Select::GROUP);
            $alreadyGrouped = false;
            foreach ($group as $groupFieldKey => $groupField) {
                if ($groupField == $this->getIdFieldName()) {
                    $alreadyGrouped = true;
                    break;
                }
            }

            if (!$alreadyGrouped) {
                $select = $this->getSelect();
                $select->group($this->getIdFieldName());
            }
        } catch (Zend_Db_Select_Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addRegionColumn()
    {
        $this->joinRegionTable();
        $select = $this->getSelect();
        $select->columns(['region' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `rrt`.`region` SEPARATOR \',\')')]);
        $this->groupByMainField();

        return $this;
    }

    /**
     * @return $this
     */
    protected function addRegionIdColumn()
    {
        $this->joinRegionIdTable();
        $select = $this->getSelect();
        $select->columns(
            ['region_id' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `rridt`.`region_id` SEPARATOR \',\')')]
        );
        $this->groupByMainField();

        return $this;
    }

    /**
     * @return $this
     */
    protected function addZipsColumns()
    {
        $this->joinZipsTables();
        $select = $this->getSelect();
        $select->columns(
            [
                'zip'         => new \Zend_Db_Expr(
                    'GROUP_CONCAT(DISTINCT IF(`zt`.`inverted`=1, "!", ""),`zt`.`zip` SEPARATOR \',\')'
                ),
                'zip_from_to' => new \Zend_Db_Expr(
                    '
                    CASE  
                       WHEN `main_table`.`zip_format` LIKE "' . ZipCodeManager::NUMERIC_FORMAT . '" THEN (GROUP_CONCAT(DISTINCT IF(`zdtn`.`inverted`=1, "!", ""),`zdtn`.`from`, \'-\', `zdtn`.`to` SEPARATOR \',\')) 
                       WHEN `main_table`.`zip_format` LIKE "' . ZipCodeManager::ALPHANUMERIC_FORMAT . '" THEN (GROUP_CONCAT(DISTINCT IF(`zdtan`.`inverted`=1, "!", ""),`zdtan`.`from`, \'-\', `zdtan`.`to` SEPARATOR \',\'))
                       WHEN `main_table`.`zip_format` LIKE "' . ZipCodeManager::ALPHANUMERIC_FORMAT_UK . '" THEN (GROUP_CONCAT(DISTINCT IF(`zdtanuk`.`inverted`=1, "!", ""),`zdtanuk`.`from`, \'-\', `zdtanuk`.`to` SEPARATOR \',\'))
                       WHEN `main_table`.`zip_format` LIKE "' . ZipCodeManager::ALPHANUMERIC_FORMAT_NL . '" THEN (GROUP_CONCAT(DISTINCT IF(`zdtannl`.`inverted`=1, "!", ""),`zdtannl`.`from`, \'-\', `zdtannl`.`to` SEPARATOR \',\'))
                    END
                    '
                ),
            ]
        );
        $this->groupByMainField();

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addStoreIdsColumns()
    {
        $this->joinStoreTable();
        $select = $this->getSelect();
        $select->columns(
            ['store_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT `store`.`store_id` SEPARATOR \',\')')]
        );
        $this->groupByMainField();

        return $this;
    }
}
