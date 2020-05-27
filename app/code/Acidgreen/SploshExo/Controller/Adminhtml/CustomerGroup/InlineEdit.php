<?php

namespace Acidgreen\SploshExo\Controller\Adminhtml\CustomerGroup;

use Acidgreen\SploshExo\Model\SploshCustomerGroupFactory;

class InlineEdit extends \Magento\Backend\App\Action
{

    /**
     * @var SploshCustomerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param SploshCustomerGroupFactory $customerGroupFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        SploshCustomerGroupFactory $customerGroupFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerGroupFactory = $customerGroupFactory;
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
                    $model = $this->customerGroupFactory->create()->load($modelid);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                        $model->save();
                        
                        $this->_eventManager->dispatch('splosh_customer_group_save_after', [
                        	'splosh_customer_group' => $model
                        ]);
                    } catch (\Exception $e) {
                        $messages[] = "[Splosh Customer Group ID: {$modelid}]  {$e->getMessage()}";
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
