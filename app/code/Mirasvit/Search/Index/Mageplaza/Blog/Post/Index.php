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



namespace Mirasvit\Search\Index\Mageplaza\Blog\Post;

use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Mageplaza / Blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'mageplaza_blog_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'name'              => __('Name'),
            'short_description' => __('Short Description'),
            'post_content'      => __('Content'),
            'meta_title'        => __('Meta Title'),
            'meta_keywords'     => __('Meta Keywords'),
            'meta_description'  => __('Meta Description'),
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
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('enabled', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        if ($entityIds) {
            $collection->addFieldToFilter('post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit);

        $collection->getSelect()->order('post_id');

        return $collection;
    }
}
