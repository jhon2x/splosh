<?php

namespace Splosh\SalesRep\Controller\Adminhtml\Location;

use Magento\Backend\App\Action;
use Splosh\SalesRep\Model\LocationFactory;
use Splosh\SalesRep\Helper\Structure;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    /**
     * @var LocationFactory
     */
    protected $locationModel;

    /**
     * @var Structure
     */
    protected $structureHelper;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param LocationFactory $locationModel
     * @param Structure $structureHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        LocationFactory $locationModel,
        Structure $structureHelper,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->locationModel = $locationModel->create();
        $this->structureHelper = $structureHelper;
        $this->columns = $structureHelper->getColumns();
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $location = $this->locationModel;

            if (!empty($id)) {
                $location->load($id);
            }

            foreach ($this->columns as $item) {
                $columnValue = $data[$item];
                if (is_array($columnValue)) {
                    $columnValue = array_values($columnValue);
                    $columnValue = implode(',', $columnValue);
                }

                $location->setData($item, $columnValue);
            }

            $location->save();

            if ($location->getId()) {
                $this->messageManager->addSuccess(__('Successfully saved mapping.'));
            }

            if ($location->getId()) {
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }

        } catch (\Exception $e) {
            $this->logger->error($e->__toString());
            $this->messageManager->addError('Error saving mapping: ' . $e->getMessage());
        }

        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Set Redirect
     */
    protected function redirect()
    {
        $id = $this->getRequest()->getParam('id');
        $args = [];
        if (!empty($id)) {
            $redirectUrl = '*/*/edit';
            $args = ['id' => $id];
        } elseif ($this->locationModel->getId()) {
            $redirectUrl = '*/*/edit';
            $args = ['id' => $this->locationModel->getId()];
        } else {
            $redirectUrl = '*/*/index';
        }

        $this->_redirect($redirectUrl, $args);
    }
}