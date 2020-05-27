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



namespace Mirasvit\SearchLanding\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Api\Repository\PageRepositoryInterface;
use Magento\Search\Model\QueryFactory;

class Router implements RouterInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        StoreManagerInterface $storeManager,
        ActionFactory $actionFactory
    ) {
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->actionFactory = $actionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function match(RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $identifier = trim($request->getPathInfo(), '/');

        $collection = $this->pageRepository->getCollection();
        $collection->addFieldToFilter(PageInterface::IS_ACTIVE, true)
            ->addFieldToFilter(PageInterface::URL_KEY, $identifier)
            ->addStoreFilter($this->storeManager->getStore()->getId());

        if ($collection->count()) {
            /** @var PageInterface $page */
            $page = $collection->getFirstItem();

            $params = [
                PageInterface::ID            => $page->getId(),
                QueryFactory::QUERY_VAR_NAME => $page->getQueryText(),
            ];
            $request
                ->setModuleName('search_landing')
                ->setControllerName('page')
                ->setActionName('view')
                ->setParams($params)
                ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
            );
        }

        return false;
    }
}
