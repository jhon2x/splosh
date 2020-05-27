<?php

namespace Acidgreen\SploshExo\Controller\Adminhtml\Staff;

use Acidgreen\SploshExo\Model\StaffFactory;

class InlineEdit extends \Magento\Backend\App\Action
{

    /**
     * @var StaffFactory
     */
    protected $staffFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        StaffFactory $staffFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->staffFactory = $staffFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    $model = $this->staffFactory->create()->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                        $model->save();
                        
                        $this->_eventManager->dispatch('staff_save_after', [
                        	'staff' => $model
                        ]);
                    } catch (\Exception $e) {
                        $messages[] = "[Staff ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }
        
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
