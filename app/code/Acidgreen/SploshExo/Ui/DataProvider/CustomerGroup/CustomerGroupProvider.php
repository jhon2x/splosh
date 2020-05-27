<?php

namespace Acidgreen\SploshExo\Ui\DataProvider\CustomerGroup;

use Magento\Customer\Api\Data\AttributeMetadataInterface;

class CustomerGroupProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Splosh Customer Group collection
     *
     * @var \Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\Collection
     */
    protected $collection;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\CollectionFactory  $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
        	$this->getCollection()->setOrder('id','ASC');
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        $attributeList = [
            'mage_group_id'=>AttributeMetadataInterface::OPTIONS,
            'exo_group_id'=>AttributeMetadataInterface::FRONTEND_INPUT,
            'description'=>AttributeMetadataInterface::FRONTEND_INPUT
        ];

        foreach ($attributeList as $attributeCode => $attributeType) {
            foreach ($items['items'] as &$item) {
                if (isset($item[$attributeCode]) && $attributeType == AttributeMetadataInterface::OPTIONS) {
                    $item[$attributeCode] = explode(',', $item[$attributeCode]);
                }elseif($attributeType == AttributeMetadataInterface::OPTIONS){
                    $item[$attributeCode] = [2];
                }
            }
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items['items'])
        ];
    }

}
