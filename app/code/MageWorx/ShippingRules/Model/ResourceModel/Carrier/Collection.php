<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel\Carrier;

use MageWorx\ShippingRules\Model\Carrier as CarrierModel;

/**
 * Class Collection
 */
class Collection extends \MageWorx\ShippingRules\Model\ResourceModel\AbstractCollection
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_shippingrules_carriers_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'carriers_collection';

    /**
     * Store associated with carrier entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table'    => CarrierModel::CARRIER_TABLE_NAME . '_store',
            'main_table_id_field'   => 'carrier_id',
            'linked_table_id_field' => 'entity_id',
            'entity_id_field'       => 'store_id',
        ]
    ];

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MageWorx\ShippingRules\Model\Carrier',
            'MageWorx\ShippingRules\Model\ResourceModel\Carrier'
        );
        $this->_map['fields']['carrier_id'] = 'main_table.carrier_id';
        $this->_setIdFieldName('carrier_id');
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
    protected function _toOptionArray($valueField = 'carrier_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    public function toOptionArray($valueField = 'carrier_id', $labelField = 'name', $additional = [])
    {
        return $this->_toOptionArray($valueField, $labelField, $additional);
    }
}
