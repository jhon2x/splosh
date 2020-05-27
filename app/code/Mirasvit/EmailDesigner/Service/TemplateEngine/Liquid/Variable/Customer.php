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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class Customer extends AbstractVariable
{
    protected $supportedTypes = ['Magento\Customer\Model\Customer'];

    protected $whitelist = [
        'getName'
    ];

    /**
     * Constructor
     *
     * @param CustomerFactory           $customerFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        parent::__construct();

        $this->customerFactory = $customerFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Customer model.
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($this->context->getData('customer')) {
            return $this->context->getData('customer');
        }

        $customer = $this->customerFactory->create();
        if ($this->context->getData('customer_id')) {
            $customer->load($this->context->getData('customer_id'));
            $this->context->setData('customer', $customer);
        }

        return $customer;
    }

    /**
     * Get customer first name (only for registered customers)
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getCustomer()->getData('firstname');
    }
}
