<?php

namespace Splosh\ProductLabels\Model\Source;

use Splosh\ProductLabels\Model\ResourceModel\Label\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class ProductLabels
 * @package Splosh\ProductLabels\Model\Source
 */
class ProductLabels extends AbstractSource
{
    /**
     * @var \Splosh\ProductLabels\Model\ResourceModel\Label\Collection;
     */
    protected $collection;

    /**
     * ProductLabels constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options[] = ['label' => __('Select Product Label'), 'value'=> ''];
            foreach ($this->collection as $item => $value) {
                $this->_options[] = [
                    'label' => __('(ID: ' . $value['label_id'] . ') ' . strtoupper($value['name'])),
                    'value' => $value['label_id']
                ];
            }
        }

        return $this->_options;
    }
}