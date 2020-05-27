<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\Customer\Block\Account;

use Magento\Customer\Model\Context;

/**
 * Dashboard Customer Info
 */
class ContextWiseLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Get current website id
     *
     * @return  string
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    public function getWebsiteCode(){
        $params = $_SERVER;
        $multi = array_key_exists(\Magento\Store\Model\StoreManager::PARAM_RUN_CODE, $params);
        return $multi ? $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] : '';
    }
}
