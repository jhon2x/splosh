<?php

namespace Splosh\ProductLabels\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Splosh\ProductLabels\Model\LabelFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 * @package Splosh\ProductLabels\Controller\Adminhtml\Action
 */
class Delete extends Action
{
    /**
     * @var LabelFactory
     */
    protected $labelModel;

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
     * @param LabelFactory $labelFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        LabelFactory $labelFactory,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->labelModel = $labelFactory->create();
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('label_id');

        try {
            $this->labelModel->load($id);
            $this->labelModel->delete();
        } catch (\Exception $e) {
            $this->logger->error($e->__toString());
        }

        $this->_redirect('*/*/');
    }
}