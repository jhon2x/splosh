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


namespace Mirasvit\Search\Index\Mirasvit\Kb\Article;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Mirasvit / Knowledge Base';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'mirasvit_kb_article';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'name'             => __('Name'),
            'text'             => __('Content'),
            'meta_title'       => __('Meta Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'article_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        /** @var \Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.article_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mirasvit\Kb\Model\ResourceModel\Article\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreIdFilter($storeId)
            ->addFieldToFilter('main_table.is_active', 1);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.article_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.article_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.article_id');

        return $collection;
    }
}
