<?php
namespace Acidgreen\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $eavAttribute;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;

        /**
         * SPL-441 - get the force_backorder attribute_id
         */
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();

        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());

        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        if ($attribute->getAttributeCode() === 'price') {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof \Magento\Store\Model\Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }
            $table = $this->resource->getTableName('catalog_product_index_price');
            $select->from(['main_table' => $table], null)
                ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
                ->where('main_table.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
                ->where('main_table.website_id = ?', $store->getWebsiteId());
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)
                ->getId();
            $table = $this->resource->getTableName(
                'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
            );

            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.value'])
                ->joinLeft(
                    ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                    'main_table.source_id = stock_index.product_id',
                    []
                )
                ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                ->where('main_table.store_id = ? ', $currentScopeId);
                /**
                 * SPL-441 - Added to show the filters,
                 * Because what prevents the filters from being shown originally in B2B
                 * Are the backorder products that have stock_status == 0
                 * @todo: use the attribute "getter" class to get the non-hardcoded attribute_id
                 */
                $store = $this->scopeResolver->getScope($currentScope);

                if (preg_match("/b2b/", $store->getCode())) {
                    $forceBackorderAttributeId = $this->eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'force_backorder');
                    $stockStatusExpr = "((stock_index.stock_status = '".Stock::STOCK_IN_STOCK."') 
                        OR (main_table.entity_id IN 
                            (SELECT entity_id FROM catalog_product_entity 
                            WHERE row_id IN 
                            (SELECT row_id FROM catalog_product_entity_int 
                                WHERE value = '1' 
                                AND store_id = '".$currentScopeId."' 
                                AND attribute_id = '".$forceBackorderAttributeId."'
                        ))))";
                    $stockStatusExpr = new \Zend_Db_Expr($stockStatusExpr);
                    $subSelect->where($stockStatusExpr);
                } else {
                    $subSelect->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);
                }
                $subSelect->group(['main_table.entity_id', 'main_table.value']);

            $parentSelect = $this->getSelect();
            $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
            $select = $parentSelect;
        }

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
