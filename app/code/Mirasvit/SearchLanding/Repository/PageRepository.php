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
 * @package   mirasvit/module-search-landing
 * @version   1.0.7
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Api\Data\PageInterfaceFactory;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;
use Mirasvit\SearchLanding\Model\ResourceModel\Page\CollectionFactory;

class PageRepository implements PageRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageInterfaceFactory
     */
    private $pageFactory;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        PageInterfaceFactory $pageFactory
    ) {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->pageFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $page = $this->create();
        $page = $this->entityManager->load($page, $id);

        if (!$page->getId()) {
            return false;
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PageInterface $page)
    {
        $this->entityManager->delete($page);
    }

    /**
     * {@inheritdoc}
     */
    public function save(PageInterface $page)
    {
        return $this->entityManager->save($page);
    }
}