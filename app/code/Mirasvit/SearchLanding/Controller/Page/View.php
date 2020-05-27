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



namespace Mirasvit\SearchLanding\Controller\Page;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

class View extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var PageRepositoryInterface
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        Registry $registry,
        PageFactory $pageFactory,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        Context $context
    ) {
        $this->registry = $registry;
        $this->pageRepository = $pageRepository;
        $this->resultPageFactory = $pageFactory;

        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(PageInterface::ID);

        $page = $this->pageRepository->get($id);

        $this->registry->register('search_landing_page', $page);

        $resultPage = $this->resultPageFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->initLayout();
        $resultPage->addHandle('catalogsearch_result_index');
        $resultPage->addHandle('search_landing_page');

        if ($page->getLayoutUpdate()) {
            $resultPage->addUpdate($page->getLayoutUpdate());
        }

        parent::execute();

        $resultPage->getConfig()->getTitle()->set('fafa');
    }
}