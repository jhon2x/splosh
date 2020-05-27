<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\SploshExo\Controller\Adminhtml\CustomerGroup;

use Acidgreen\SploshExo\Model\SploshCustomerGroupFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * sploshCustomerGroupFactory
     *
     * @var SploshCustomerGroupFactory
     */
    protected $sploshCustomerGroupFactory;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param SploshCustomerGroupFactory $sploshCustomerGroupFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        SploshCustomerGroupFactory $sploshCustomerGroupFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $context
        );
        $this->sploshCustomerGroupFactory = $sploshCustomerGroupFactory;
    }

    /**
     * Create or save customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroup = null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $mageId = (int)$this->getRequest()->getParam('mage_group');
            $exoId = (int)$this->getRequest()->getParam('exo_group');
            $description = $this->getRequest()->getParam('description');
            $customerGroup = $this->sploshCustomerGroupFactory->create();

            $customerGroup->setMageGroupId($mageId);
            $customerGroup->setExoGroupId($exoId);
            $customerGroup->setDescription($description);

            $customerGroup->save($customerGroup);

            $this->messageManager->addSuccess(__('You saved the customer group mapping.'));
            $resultRedirect->setPath('acidgreen_sploshexo/customergroup');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect->setPath('acidgreen_sploshexo/customergroup/index');
        }
        return $resultRedirect;
    }
}
