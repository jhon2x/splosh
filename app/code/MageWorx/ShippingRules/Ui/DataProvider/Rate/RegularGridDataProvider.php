<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Ui\DataProvider\Rate;

use Magento\Ui\DataProvider\AbstractDataProvider;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\Grid\RegularCollectionFactory as CollectionFactory;
use MageWorx\ShippingRules\Api\Data\RateInterface;

/**
 * Class RegularGridDataProvider
 */
class RegularGridDataProvider extends AbstractDataProvider
{
    /**
     * Collection
     *
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Grid\RegularCollection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection          = $collectionFactory->create();
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        if (!empty($data['totalRecords'])) {
            $this->updateZipColumnsData($data);
        }

        return $data;
    }

    /**
     * Update data in zip columns based on selected validation mode
     *
     * @param array $data
     * @return array
     */
    private function updateZipColumnsData(&$data)
    {
        if (empty($data['items'])) {
            return $data;
        }

        foreach ($data['items'] as &$item) {
            if (!isset($item['zip_validation_mode'])) {
                continue;
            }

            switch ($item['zip_validation_mode']) {
                case RateInterface::ZIP_VALIDATION_MODE_DIAPASON:
                    $item['zip'] = null;
                    break;
                case RateInterface::ZIP_VALIDATION_MODE_PLAIN:
                    $item['zip_from_to'] = null;
                    break;
                case RateInterface::ZIP_VALIDATION_MODE_NONE:
                default:
                    $item['zip']         = null;
                    $item['zip_from_to'] = null;
            }
        }

        return $data;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}