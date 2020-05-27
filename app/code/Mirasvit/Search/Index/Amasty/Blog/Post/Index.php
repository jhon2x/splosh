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



namespace Mirasvit\Search\Index\Amasty\Blog\Post;

use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Amasty / Blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'amasty_blog_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title'         => __('Title'),
            'short_content' => __('Short Content'),
            'full_content'  => __('Full Content'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'post_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory');

        /** @var \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection */
        $collection = $collectionFactory->create()
            ->addFieldToFilter('status', 2);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('Amasty\Blog\Model\ResourceModel\Posts\CollectionFactory');

        /** @var \Amasty\Blog\Model\ResourceModel\Posts\Collection $collection */
        $collection = $collectionFactory->create();

        $collection->addStoreFilter([0, $storeId]);

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection;
    }
}
