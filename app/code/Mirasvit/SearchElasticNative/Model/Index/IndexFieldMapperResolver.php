<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchElasticNative\Model\Index;

use Magento\Framework\ObjectManagerInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\Registry;

/**
 * Unlike Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver
 * this implementation doesn't store field mapper instance
 */
class IndexFieldMapperResolver implements FieldMapperInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $fieldMappers;

    /**
     * @var Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $fieldMappers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $fieldMappers = [],
        Registry $registry
    ) {
        $this->objectManager = $objectManager;
        $this->fieldMappers = $fieldMappers;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $entityType = $this->registry->registry('indexer_id'); /** @see Mirasvit\Search\Model\Index\Indexer::reindexAll */
        if (!$entityType) {
            $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        }
        $context['entityType'] = $entityType;
        return $this->getEntity($entityType)->getFieldName($attributeCode, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesTypes($context = [])
    {
        $entityType = $this->registry->registry('indexer_id'); /** @see Mirasvit\Search\Model\Index\Indexer::reindexAll */
        if (!$entityType) {
            $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        }
        $context['entityType'] = $entityType;
        return $this->getEntity($entityType)->getAllAttributesTypes($context);
    }

    /**
     * Get instance of current field mapper
     *
     * @param string $entityType
     * @return FieldMapperInterface
     * @throws \Exception
     */
    private function getEntity($entityType)
    {
        if (empty($entityType)) {
            throw new \Exception(
                'No entity type given'
            );
        }

        if (!isset($this->fieldMappers[$entityType])) {
            throw new \LogicException(
                'There is no such field mapper: ' . $entityType
            );
        }

        $fieldMapperClass = $this->fieldMappers[$entityType];
        $fieldMapper = $this->objectManager->create($fieldMapperClass);
        if (!($fieldMapper instanceof FieldMapperInterface)) {
            throw new \InvalidArgumentException(
                'Field mapper must implement \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface'
            );
        }
        return $fieldMapper;
    }
}
