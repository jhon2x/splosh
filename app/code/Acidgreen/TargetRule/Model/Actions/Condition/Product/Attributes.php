<?php

namespace Acidgreen\TargetRule\Model\Actions\Condition\Product;

class Attributes extends \Magento\TargetRule\Model\Actions\Condition\Product\Attributes
{

    /**
     * Retrieve SELECT WHERE condition for product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\TargetRule\Model\Index $object
     * @param array &$bind
     * @return \Zend_Db_Expr|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConditionForCollection($collection, $object, &$bind)
    {
        /* @var $resource \Magento\TargetRule\Model\ResourceModel\Index */
        $attributeCode = $this->getAttribute();
        $valueType = $this->getValueType();
        $operator = $this->getOperator();
        $resource = $object->getResource();

        if ($attributeCode == 'category_ids') {
            $select = $object->select()->from(
                $resource->getTable('catalog_category_product'),
                'COUNT(*)'
            )->where(
                'product_id=e.entity_id'
            );
            if ($valueType == self::VALUE_TYPE_SAME_AS) {
                $operator = '!{}' == $operator ? '!()' : '()';
                $where = $resource->getOperatorBindCondition(
                    'category_id',
                    'category_ids',
                    $operator,
                    $bind,
                    ['bindArrayOfIds']
                );
                $select->where($where);
            } elseif ($valueType == self::VALUE_TYPE_CHILD_OF) {
                $concatenated = $resource->getConnection()->getConcatSql(['tp.path', "'/%'"]);
                $subSelect = $resource->select()->from(
                    ['tc' => $resource->getTable('catalog_category_entity')],
                    'entity_id'
                )->join(
                    ['tp' => $resource->getTable('catalog_category_entity')],
                    "tc.path " . ($operator == '!()' ? 'NOT ' : '') . "LIKE {$concatenated}",
                    []
                )->where(
                    $resource->getOperatorBindCondition(
                        'tp.entity_id',
                        'category_ids',
                        '()',
                        $bind,
                        ['bindArrayOfIds']
                    )
                );
                $select->where('category_id IN(?)', $subSelect);
            } else {
                //self::VALUE_TYPE_CONSTANT
                $value = $resource->bindArrayOfIds($this->getValue());
                $where = $resource->getOperatorCondition('category_id', $operator, $value);
                $select->where($where);
            }

            return new \Zend_Db_Expr(sprintf('(%s) > 0', $select->assemble()));
        }

        if ($valueType == self::VALUE_TYPE_CONSTANT) {
            $useBind = false;
            $value = $this->getValue();
            // split value by commas into array for operators with multiple operands
            if (($operator == '()' || $operator == '!()') && is_string($value) && trim($value) != '') {
                $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            }
        } else {
            //self::VALUE_TYPE_SAME_AS
            $useBind = true;
        }

        $attribute = $this->getAttributeObject();
        if (!$attribute) {
            return false;
        }

        if ($attribute->isStatic()) {
            $field = "e.{$attributeCode}";
            if ($useBind) {
                $where = $resource->getOperatorBindCondition($field, $attributeCode, $operator, $bind);
            } else {
                $where = $resource->getOperatorCondition($field, $operator, $value);
            }
            $where = sprintf('(%s)', $where);
        } elseif ($attribute->isScopeGlobal()) {
            $table = $attribute->getBackendTable();
            $select = $object->select()
                ->from(['table' => $table], 'COUNT(*)')
                // SPL-226 - Change from entity_id to row_id
                ->where('table.row_id = e.row_id')
                ->where('table.attribute_id=?', $attribute->getId())
                ->where('table.store_id=?', 0);
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition('table.value', $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition('table.value', $operator, $value));
            }

            $select = $resource->getConnection()->getIfNullSql($select);
            $where = sprintf('(%s) > 0', $select);
        } else {
            //scope store and website
            $valueExpr = $resource->getConnection()->getCheckSql(
                'attr_s.value_id > 0',
                'attr_s.value',
                'attr_d.value'
            );
            $table = $attribute->getBackendTable();
            $select = $object->select()->from(
                ['attr_d' => $table],
                'COUNT(*)'
            )->joinLeft(
                ['attr_s' => $table],
                $resource->getConnection()->quoteInto(
                    'attr_s.entity_id = attr_d.entity_id AND attr_s.attribute_id = attr_d.attribute_id' .
                    ' AND attr_s.store_id=?',
                    $object->getStoreId()
                ),
                []
            )->where(
                'attr_d.entity_id = e.entity_id'
            )->where(
                'attr_d.attribute_id=?',
                $attribute->getId()
            )->where(
                'attr_d.store_id=?',
                0
            );
            if ($useBind) {
                $select->where($resource->getOperatorBindCondition($valueExpr, $attributeCode, $operator, $bind));
            } else {
                $select->where($resource->getOperatorCondition($valueExpr, $operator, $value));
            }

            $where = sprintf('(%s) > 0', $select);
        }
        return new \Zend_Db_Expr($where);
    }
}
