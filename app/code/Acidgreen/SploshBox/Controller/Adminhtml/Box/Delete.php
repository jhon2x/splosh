<?php


namespace Acidgreen\SploshBox\Controller\Adminhtml\Box;

class Delete extends \Acidgreen\SploshBox\Controller\Adminhtml\Box
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('box_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Acidgreen\SploshBox\Model\Box');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the Box.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['box_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Box to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
