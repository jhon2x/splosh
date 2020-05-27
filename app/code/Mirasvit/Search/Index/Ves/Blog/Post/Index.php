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


namespace Mirasvit\Search\Index\Ves\Blog\Post;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Ves / Blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'ves_blog_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title'            => __('Title'),
            'content'          => __('Content'),
            'short_content'    => __('Short Content'),
            'page_title'       => __('Page Title'),
            'page_keywords'    => __('Page Keywords'),
            'page_description' => __('Page Description'),
            'tags'             => __('Tags'),
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
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.post_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ves\Blog\Model\ResourceModel\Post\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Blog\Model\ResourceModel\Post\CollectionFactory');

        $storeManager = $this->context->getObjectManager()
            ->create('Magento\Store\Model\Store');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeManager->load($storeId));

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.post_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.post_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('post_id');

        return $collection;
    }
}
