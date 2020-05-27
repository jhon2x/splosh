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


namespace Mirasvit\Search\Index\Magento\Cms\Page;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

/**
 * @method array getIgnoredPages()
 */
class Index extends AbstractIndex
{
    /**
     * @var PageCollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        PageCollectionFactory $collectionFactory,
        Context $context,
        $dataMappers
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Magento / Cms Page';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'magento_cms_page';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title'            => __('Title'),
            'content'          => __('Content'),
            'content_heading'  => __('Content Heading'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'page_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.page_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);

        $ignored = $this->getModel()->getProperty('ignored_pages');
        if (is_array($ignored) && count($ignored)) {
            $collection->addFieldToFilter('identifier', ['nin' => $ignored]);
        }

        if ($entityIds) {
            $collection->addFieldToFilter('page_id', $entityIds);
        }

        $collection
            ->addFieldToFilter('page_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('page_id', 'asc');

        return $collection;
    }
}
