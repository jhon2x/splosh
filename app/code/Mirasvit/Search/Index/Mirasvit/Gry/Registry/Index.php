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


namespace Mirasvit\Search\Index\Mirasvit\Gry\Registry;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Index extends AbstractIndex
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Mirasvit / Gift Registry';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'mirasvit_gry_registry';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'uid'           => __('ID'),
            'name'          => __('Name'),
            'description'   => __('Description'),
            'location'      => __('Event Location'),
            'firstname'     => __('Registrant First Name'),
            'lastname'      => __('Registrant Last Name'),
            'co_firstname'  => __('Co-Registrant First Name'),
            'co_lastname'   => __('Co-Registrant Last Name'),
            'email'         => __('Registrant email'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'registry_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collectionFactory = ObjectManager::getInstance()
            ->create('Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        /** @var \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection $collection */
        $collection = $collectionFactory->create();
        $collection->addWebsiteFilter()
            ->addIsActiveFilter();

        // Check if a search performed by UID
        /** @var \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection $uidCollection */
        $uidCollection = $collectionFactory->create();

        $uidCollection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        if ($uidCollection->getSize()) {
            $collection->addFieldToFilter('uid', $this->context->getRequest()->getParam('q'));
        } else {
            // Otherwise search only within pulic registries
            $collection->addFieldToFilter('is_public', 1);
        }

        $this->context->getSearcher()->joinMatches($collection, 'main_table.registry_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $websiteId = $this->context->getStoreManager()->getStore($storeId)->getWebsiteId();
        $collectionFactory = $this->context->getObjectManager()
            ->create('Mirasvit\Giftr\Model\ResourceModel\Registry\CollectionFactory');

        /** @var \Mirasvit\Giftr\Model\ResourceModel\Registry\Collection $collection */
        $collection = $collectionFactory->create();
        $collection->addFieldToFilter('main_table.website_id', $websiteId)
            ->addIsActiveFilter();

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.registry_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.registry_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.registry_id');

        return $collection;
    }
}
