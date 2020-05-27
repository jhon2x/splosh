<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel\Rate;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Event\ManagerInterface;
use MageWorx\ShippingRules\Model\ZipCodeManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\ShippingRules\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class ExportCollection
 */
class ExportCollection extends \MageWorx\ShippingRules\Model\ResourceModel\Rate\Grid\RegularCollection
{
    /**
     * ExportCollection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param TimezoneInterface $date
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        TimezoneInterface $date,
        StoreManagerInterface $storeManager,
        Data $helper,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'MageWorx\ShippingRules\Model\Carrier\Method\Rate',
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $date,
            $storeManager,
            $helper,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function joinLinkedTables()
    {
        $this->addCountryIdColumn();
        $this->addRegionColumn();
        $this->addRegionIdColumn();
        $this->addZipsColumns();
        $this->addStoreIdsColumns();

        $this->_eventManager->dispatch(
            'mageworx_rates_export_collection_join_linked_tables_after',
            [
                'collection' => $this
            ]
        );

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
                'plain_zip_codes'    => new \Zend_Db_Expr(
                    'GROUP_CONCAT(DISTINCT IF(`zt`.`inverted`=1, "!", ""),`zt`.`zip` SEPARATOR \',\')'
                ),
                'zip_code_diapasons' => new \Zend_Db_Expr(
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
}