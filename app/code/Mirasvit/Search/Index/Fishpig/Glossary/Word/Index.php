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



namespace Mirasvit\Search\Index\Fishpig\Glossary\Word;

use Mirasvit\Search\Model\Index\AbstractIndex;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'FishPig / Glossary';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'fishpig_glossary_word';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'word'             => __('Word'),
            'short_definition' => __('Short Definiton'),
            'definition'       => __('Definiton'),
            'meta_title'       => __('Meta Title'),
            'meta_description' => __('Meta Description')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'word_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('FishPig\Glossary\Model\ResourceModel\Word\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addFieldToFilter('is_active', 1);

        $this->context->getSearcher()->joinMatches($collection, 'main_table.word_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collectionFactory = $this->context->getObjectManager()
            ->create('FishPig\Glossary\Model\ResourceModel\Word\CollectionFactory');

        $collection = $collectionFactory->create();

        $collection->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('word_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('word_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('word_id');

        return $collection;
    }
}