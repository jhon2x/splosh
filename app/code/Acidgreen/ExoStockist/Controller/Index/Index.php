<?php

namespace Acidgreen\ExoStockist\Controller\Index;

use Acidgreen\ExoStockist\Helper\Api\Api as ApiHelper;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_apiHelper;

    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ApiHelper $apiHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_apiHelper = $apiHelper;
        parent::__construct($context);
    }
    /**
     * Execute action.
     */
    public function execute()
    {
        //if (!$this->getRequest()->getParam('postcode') || !$this->getRequest()->getParam('stockcode')) {
        //    $resultRedirect = $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        //    return $resultRedirect;
        //}
        $this->_coreRegistry->register('postcode', $this->getRequest()->getParam('postcode'));

        $resp = $this->_apiHelper->getExoStockists($this->getRequest()->getParam('postcode'), $this->getRequest()->getParam('stockcode'));

        $this->_coreRegistry->register('jsonstockist', $resp);

        $result = $this->resultJsonFactory->create();

        return $result->setData($resp);
    }
}
