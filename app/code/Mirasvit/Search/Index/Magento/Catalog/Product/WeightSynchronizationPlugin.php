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



namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Magento\Eav\Model\Entity;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;

class WeightSynchronizationPlugin
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var EavAttribute
     */
    private $eavAttribute;

    public function __construct(
        Entity $entity,
        AttributeCollectionFactory $attributeCollectionFactory,
        EavAttribute $eavAttribute
    ) {
        $this->entity = $entity;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexInterface $index
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterSave(IndexRepositoryInterface $indexRepository, IndexInterface $index)
    {
        if ($index->getIdentifier() != 'catalogsearch_fulltext') {
            return;
        }

        $attributes = $index->getAttributes();

        if (!is_array($attributes) || count($attributes) == 0) {
            return;
        }

        $entityTypeId = $this->entity->setType(Product::ENTITY)->getTypeId();

        $collection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('is_searchable', 1);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            if (!array_key_exists($attribute->getAttributeCode(), $attributes) && $attribute->getIsSearchable()) {
                $attribute->setIsSearchable(0)
                    ->save();
            }
        }

        foreach ($attributes as $code => $weight) {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->eavAttribute->loadByCode($entityTypeId, $code);
            if (!$attribute->getId()) {
                continue;
            }
            if ($attribute->getSearchWeight() != $weight || !$attribute->getIsSearchable()) {
                $attribute->setSearchWeight($weight)
                    ->setIsSearchable(1)
                    ->save();
            }
        }
    }
}
