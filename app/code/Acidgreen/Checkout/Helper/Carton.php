<?php

namespace Acidgreen\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Carton extends AbstractHelper
{
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Carton constructor.
     * @param Context $context
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);

        $this->attributeRepository = $attributeRepository;
        $this->resource = $resource;
    }

    public function getExoCtnSizeAttribute()
    {
        $attributeCode = 'exo_ctn_size';
        $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);

        return $attribute;
    }

    public function getProductStoreAttributes($quote)
    {
        $exoCtnSizeAttribute = $this->getExoCtnSizeAttribute();
        $storeId = $quote->getStoreId();
        $cartProductIds = $this->getCartProductIds($quote);

        $table = 'catalog_product_entity_' . $exoCtnSizeAttribute->getBackendType();
        $parentTable = 'catalog_product_entity';
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $table = $connection->getTableName($table);
        $parentTable = $connection->getTableName($parentTable);
        $sql = 'SELECT cpev.*, cpe.entity_id FROM '.$table.' cpev'
            .' INNER JOIN '.$parentTable.' cpe ON cpev.row_id = cpe.row_id '
            .' WHERE attribute_id='.$exoCtnSizeAttribute['attribute_id'].' AND store_id='.$storeId.' AND cpev.row_id IN ('
            . 'SELECT row_id FROM catalog_product_entity WHERE entity_id IN ('.$cartProductIds.'))';

        $productStoreAttributes = [];
        $result = $connection->fetchAll($sql);

        foreach ($result as $row) {
            $productStoreAttributes[$row['entity_id']] = $row;
        }

        return $productStoreAttributes;
    }

    protected function getCartProductIds($quote)
    {
        $productIds = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        $productIds = implode(',', $productIds);

        return $productIds;
    }
}
