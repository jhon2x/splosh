<?php

namespace Acidgreen\Theme\Block\Html\Salesrep;

class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

}
