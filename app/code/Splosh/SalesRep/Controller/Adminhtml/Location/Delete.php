<?php

namespace Splosh\SalesRep\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Splosh\SalesRep\Model\Location;
use Psr\Log\LoggerInterface;

class Delete extends Action
{
    /**
     * @var Location
     */
    protected $locationModel;

    /**
     * @var
     */
    protected $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Location $locationModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Location $locationModel,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->locationModel = $locationModel;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $this->locationModel->load($id);
            $this->locationModel->delete();
        } catch (\Exception $e) {
            $this->logger->error($e->__toString());
        }

        $this->_redirect('*/*/index');
    }
}