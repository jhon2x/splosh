<?php

namespace Splosh\SalesRep\Model\Import;

use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Splosh\SalesRep\Model\Import\Location\RowValidatorInterface as ValidatorInterface;
use Splosh\SalesRep\Helper\Structure;
use Splosh\SalesRep\Helper\Data;

class Location extends AbstractEntity
{
    /**
     * @var array
     */
    protected $_permanentAttributes = [Structure::STAFF_ID];

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $validColumnNames = [
        Structure::ID,
        Structure::STAFF_ID,
        Structure::STATE,
        Structure::POSTCODE,
        Structure::SUBURB
    ];

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Location constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        Data $helper
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'staff_location';
    }

    /**
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
        }
        $this->_validatedRows[$rowNumber] = true;
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        $this->saveEntity();
        return true;
    }

    /**
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * @return $this
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listTitle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_STAFF_ID_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowTitle= $rowData[Structure::ID];
                $listTitle[] = $rowTitle;
                $entityList[$rowTitle][] = [
                    Structure::ID => $rowData[Structure::ID],
                    Structure::STAFF_ID => $rowData[Structure::STAFF_ID],
                    Structure::STATE => $this->helper->getStatesId($rowData[Structure::STATE]),
                    Structure::POSTCODE => $rowData[Structure::POSTCODE],
                    Structure::SUBURB => $rowData[Structure::SUBURB],
                ];
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listTitle) {
                    if ($this->deleteEntityFinish(array_unique(  $listTitle), Structure::TABLE)) {
                        $this->saveEntityFinish($entityList, Structure::TABLE);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList, Structure::TABLE);
            }
        }
        return $this;
    }

    /**
     * @param array $entityData
     * @param $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn,[
                    Structure::ID,
                    Structure::STAFF_ID,
                    Structure::STATE,
                    Structure::POSTCODE,
                    Structure::SUBURB
                ]);
            }
        }
        return $this;
    }
}
