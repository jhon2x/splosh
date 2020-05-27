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


namespace Mirasvit\Search\Index\External\Wordpress\Post;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Index\External\Wordpress\Post\CollectionFactory as PostCollectionFactory;

class Index extends AbstractIndex
{
    /**
     * @var CollectionFactory
     */
    private $postCollectionFactory;

    public function __construct(
        PostCollectionFactory $postCollectionFactory,
        Context $context,
        $dataMappers
    ) {
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'External / Wordpress Blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'external_wordpress_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'post_title'   => __('Post Title'),
            'post_content' => __('Post Content'),
            'post_excerpt' => __('Post Excerpt'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeId($attributeCode)
    {
        $attributes = array_keys($this->getAttributes());

        return array_search($attributeCode, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'ID';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);

        $this->context->getSearcher()->joinMatches($collection, 'ID');

        return $collection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = $this->postCollectionFactory->create(['index' => $this]);

        if ($entityIds) {
            $collection->addFieldToFilter('ID', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('ID', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('ID');

        return $collection;
    }

    /**
     * Return new connection to wordpress database
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        if ($this->getModel()->getProperty('db_connection_name')) {
            $connectionName = $this->getModel()->getProperty('db_connection_name');

            $connection = $this->context->getResourceConnection()->getConnection($connectionName);

            return $connection;
        }

        return $this->context->getResourceConnection()->getConnection();
    }
}
