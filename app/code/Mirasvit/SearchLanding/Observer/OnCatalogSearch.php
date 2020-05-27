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



namespace Mirasvit\SearchLanding\Observer;

use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Search\Model\QueryFactory;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\StoreManagerInterface;

class OnCatalogSearch implements ObserverInterface
{
    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var QueryFactory
     */
    private $query;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    public function __construct(
        HttpResponse $response,
        PageRepositoryInterface $pageRepository,
        QueryFactory $queryFactory,
        UrlFactory $urlFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->response = $response;
        $this->pageRepository = $pageRepository;
        $this->query = $queryFactory->get();
        $this->urlFactory = $urlFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Observer for controller_action_postdispatch_catalogsearch_result_index
     *
     * @param EventObserver $observer
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $queryText = strip_tags($this->query->getQueryText());

        $collection = $this->pageRepository->getCollection();
        $collection->addFieldToFilter(PageInterface::IS_ACTIVE, true)
            ->addFieldToFilter('query_text', $queryText)
            ->addStoreFilter($this->storeManager->getStore()->getId());

        if ($collection->count()) {
            $page = $collection->getFirstItem();
            $url = $this->urlFactory->create()->getUrl($page->getUrlKey());
            $this->response->setRedirect($url);
            return true;
        }

        return false;
    }
}
