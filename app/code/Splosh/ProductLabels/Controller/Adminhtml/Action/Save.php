<?php

namespace Splosh\ProductLabels\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Splosh\ProductLabels\Model\LabelFactory;

/**
 * Class Save
 * @package Splosh\ProductLabels\Controller\Adminhtml\Action
 */
class Save extends Action
{
    const PRODUCT_LABELS_GRID_RESOURCE = 'Splosh_ProductLabels::grid';

    /**
     * @var \Splosh\ProductLabels\Model\Label
     */
    protected $labelModel;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param LabelFactory $labelFactory
     */
    public function __construct(Action\Context $context, LabelFactory $labelFactory)
    {
        parent::__construct($context);
        $this->labelModel = $labelFactory->create();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        $params = $this->getRequest()->getPostValue();
        $params = $this->prepareData($params);
        if ($params) {
            $labelModel = $this->labelModel;
            $label_id = $this->getRequest()->getParam('label_id');

            try {
                if ($label_id) $labelModel->load($label_id);
                $labelModel->setData($params);
                $labelModel->save();

                $this->messageManager->addSuccessMessage(__('Product Label Saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function prepareData($data)
    {
        if (!empty($data['image'][0]['file'])) {
            $data['image'] = $data['image'][0]['file'];
        } elseif (!empty($data['image'][0]['path'])) {
            $data['image'] = $data['image'][0]['path'];
        } else {
            $data['image'] = '';
        }

        return $data;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::PRODUCT_LABELS_GRID_RESOURCE);
    }
}