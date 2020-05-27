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



namespace Mirasvit\Search\Index\Aheadworks\Blog\Post;

use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Aheadworks / Blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'aheadworks_blog_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title'            => __('Title'),
            'short_content'    => __('Content Heading'),
            'content'          => __('Content'),
            'meta_title'       => __('Meta Title'),
            'meta_description' => __('Meta Description'),
            'tag_names'        => __('Tags'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory');

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 'publication');

        $this->context->getSearcher()->joinMatches($collection, 'main_table.id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Aheadworks\Blog\Model\ResourceModel\Post\CollectionFactory');

        /** @var \Aheadworks\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('id');

        return $collection;
    }
}
