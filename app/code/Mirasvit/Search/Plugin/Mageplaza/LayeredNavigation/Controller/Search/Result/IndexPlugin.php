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



namespace Mirasvit\Search\Plugin\Mageplaza\LayeredNavigation\Controller\Search\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

class IndexPlugin extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog session
     * @var Session
     */
    private $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @type \Magento\Framework\Json\Helper\Data
     */
    private $_jsonHelper;

    /**
     * @type \Magento\CatalogSearch\Helper\Data
     */
    private $_helper;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Catalog\Model\Session             $catalogSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Search\Model\QueryFactory         $queryFactory
     * @param \Magento\Catalog\Model\Layer\Resolver      $layerResolver
     * @param \Magento\CatalogSearch\Helper\Data         $helper
     * @param \Magento\Framework\Json\Helper\Data        $jsonHelper
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        \Magento\CatalogSearch\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->_storeManager   = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory   = $queryFactory;
        $this->layerResolver   = $layerResolver;
        $this->_jsonHelper     = $jsonHelper;
        $this->_helper         = $helper;
    }

    /**
     * @return void
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_helper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
                if ($query->getRedirect()) {
                    $this->getResponse()->setRedirect($query->getRedirect());

                    return;
                }
            }

            $this->_helper->checkNotes();

            if ($this->getRequest()->isAjax()) {
                $this->_view->loadLayout();
                $navigation = $this->_view->getLayout()->getBlock('catalogsearch.leftnav');
                $products   = $this->_view->getLayout()->getBlock('searchindex.result');
                $result     = ['products' => $products->toHtml(), 'navigation' => $navigation->toHtml()];
                $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
            } else {
                $this->_view->loadLayout();
                $this->_view->renderLayout();
            }
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }

    public function execute()
    {
    }
}
