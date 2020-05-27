<?php


namespace Acidgreen\SploshBox\Controller\Adminhtml\Box;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\AbstractAction;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('box_id');
        
            $model = $this->_objectManager->create('Acidgreen\SploshBox\Model\Box')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This Box no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the Box.'));
                $this->dataPersistor->clear('acidgreen_sploshbox_box');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['box_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Box.'));
            }
        
            $this->dataPersistor->set('acidgreen_sploshbox_box', $data);
            return $resultRedirect->setPath('*/*/edit', ['box_id' => $this->getRequest()->getParam('box_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
