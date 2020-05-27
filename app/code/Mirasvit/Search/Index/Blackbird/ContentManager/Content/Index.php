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



namespace Mirasvit\Search\Index\Blackbird\ContentManager\Content;

use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Blackbird / Content Manager';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'blackbird_contentmanager_content';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $attributes = [
            'title' => __('Title'),
        ];

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'entity_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory');

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection
            ->addAttributeToFilter('status', 1)
            ->addAttributeToSelect('*');

        if (count($this->getSearchableTypes())) {
            $collection->addContentTypeFilter($this->getSearchableTypes());
        }

        $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory');

        /** @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->addAttributeToSelect('*')
            ->setPageSize($limit)
            ->setOrder('entity_id');

        return $collection;
    }

    /**
     * @return array
     */
    private function getSearchableTypes()
    {
        $types = $this->getModel()->getProperty('content_types');

        $types = is_array($types) ? array_filter($types) : [];

        return $types;
    }
}
