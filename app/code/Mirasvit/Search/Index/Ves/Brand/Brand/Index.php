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


namespace Mirasvit\Search\Index\Ves\Brand\Brand;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Ves / Brand';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'ves_brand_brand';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'name'             => __('Title'),
            'description'      => __('Content'),
            'page_title'       => __('Page Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'brand_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = ObjectManager::getInstance()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.brand_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collection */
        $collectionFactory = $this->context->getObjectManager()
            ->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

        $collection = $collectionFactory->create()
            ->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.brand_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.brand_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('brand_id');

        return $collection;
    }
}
