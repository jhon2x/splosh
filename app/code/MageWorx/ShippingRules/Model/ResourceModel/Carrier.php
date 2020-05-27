<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use Magento\Framework\Stdlib\StringUtils;
use MageWorx\ShippingRules\Helper\Data as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;

/**
 * Class Carrier
 */
class Carrier extends AbstractResourceModel
{
    /**
     * Store associated with carrier entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table' => CarrierModel::CARRIER_TABLE_NAME . '_store',
            'ref_id_field'       => 'entity_id',
            'entity_id_field'    => 'store_id',
        ]
    ];

    /**
     * @var Method\CollectionFactory
     */
    protected $methodsCollectionFactory;

    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @param Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param StoreManagerInterface $storeManager
     * @param StoreResolver $storeResolver
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Method\CollectionFactory $methodsCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Helper $helper,
        StoreManagerInterface $storeManager,
        StoreResolver $storeResolver,
        \MageWorx\ShippingRules\Model\ResourceModel\Method\CollectionFactory $methodsCollectionFactory,
        $connectionName = null
    ) {
        $this->methodsCollectionFactory = $methodsCollectionFactory;
        $this->storeResolver            = $storeResolver;
        parent::__construct($context, $string, $helper, $storeManager, $connectionName);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    public function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);
        $this->validateModel($object);

        return $this;
    }

    /**
     * Validate model required fields
     *
     * @param AbstractModel $object
     * @throws LocalizedException
     */
    public function validateModel(AbstractModel $object)
    {
        /** @var Carrier $object */
        if (!$object->getCarrierCode()) {
            throw new LocalizedException(__('Carrier Code is required'));
        }
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::CARRIER_TABLE_NAME, 'carrier_id');
    }

    /**
     * Add customer group ids and store ids to rule data after load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\Carrier $object */
        parent::_afterLoad($object);
        $storeId = $this->storeResolver->getCurrentStoreId();

        try {
            $label = $object->getStoreLabel($storeId);
        } catch (LocalizedException $localizedException) {
            $label = null;
        }

        if (!empty($label)) {
            $object->setTitle($label);
        }

        return $this;
    }

    /**
     * Get store labels table
     *
     * @return string
     */
    protected function getStoreLabelsTable()
    {
        return $this->getTable(CarrierModel::CARRIER_LABELS_TABLE_NAME);
    }

    /**
     * Get reference id column name from the labels table
     *
     * @return string
     */
    protected function getStoreLabelsTableRefId()
    {
        return 'carrier_id';
    }
}
