<?php

namespace Acidgreen\SploshExo\Controller\Adminhtml\CustomerGroup;

use Magento\Backend\App\Action\Context;
use Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\CollectionFactory;
use Acidgreen\SploshExo\Model\ResourceModel\SploshCustomerGroup\Collection as SploshCustomerGroupCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * @param SploshCustomerGroupCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(SploshCustomerGroupCollection $collection)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        $mappingsDeleted = 0;
        $ids = '';
        foreach ($collection->getAllIds() as $customerId) {
            $ids = $ids . $customerId. ',';
            $mappingsDeleted++;
        }
        $ids = trim($ids, ',');

        $connection = $resource->getConnection();

        $sql = sprintf("DELETE FROM splosh_customer_group
                  WHERE id IN ( %s )", $ids) ;

        $connection->query($sql);

        if ($mappingsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $mappingsDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
