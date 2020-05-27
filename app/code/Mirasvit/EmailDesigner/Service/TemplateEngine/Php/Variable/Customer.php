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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class Customer
{
    /**
     * Constructor
     *
     * @param CustomerFactory           $customerFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Context                   $context
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Context $context
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->context = $context;
    }

    /**
     * Customer model
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($this->context->getData('customer')) {
            return $this->context->getData('customer');
        } elseif ($this->context->getData('customer_id')) {
            $customer = $this->customerFactory->create()
                ->load($this->context->getData('customer_id'));
            $this->context->setData('customer', $customer);
        }

        return $this->context->getData('customer');
    }

    /**
     * Random variables
     *
     * @return array
     */
    public function getRandomVariables()
    {
        $variables = [];
        $collection = $this->customerCollectionFactory->create();
        if ($collection->getSize()) {
            $collection->getSelect()->limit(1, rand(0, $collection->getSize() - 1));

            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $collection->getFirstItem();

            if ($customer->getId()) {
                $variables['customer_id'] = $customer->getId();
                $variables['customer_name'] = $customer->getName();
                $variables['customer_email'] = $customer->getEmail();
            }
        }

        return $variables;
    }
}
