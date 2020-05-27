<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\EventData\Customer;


use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\EventData\CustomerData;
use Magento\Sales\Model\ResourceModel\Sale\Collection as SaleCollection;
use Magento\Sales\Model\ResourceModel\Sale\CollectionFactory as SaleCollectionFactory;

abstract class CustomerAbstractAttribute
{
    /**
     * @var SaleCollectionFactory
     */
    private $saleCollectionFactory;

    public function __construct(SaleCollectionFactory $saleCollectionFactory)
    {
        $this->saleCollectionFactory = $saleCollectionFactory;
    }

    /**
     * Retrieve customer totals.
     *
     * @param AbstractModel $dataObject
     *
     * @return DataObject
     */
    protected function getCustomerTotals(AbstractModel $dataObject)
    {
        /** @var Customer $model */
        /** @var SaleCollection $sale */
        $sale = $this->saleCollectionFactory->create();
        $model = $dataObject->getData(CustomerData::IDENTIFIER);

        $sale->setOrderStateFilter(Order::STATE_CANCELED, true);

        if ($model && $model->getId()) {
            $sale->setCustomerIdFilter($model->getId());
        } else {
            $sale->addFieldToFilter(
                InstanceEventInterface::PARAM_CUSTOMER_EMAIL,
                $dataObject->getData(InstanceEventInterface::PARAM_CUSTOMER_EMAIL)
            );
        }

        $sale->load();

        return $sale->getTotals();
    }
}
