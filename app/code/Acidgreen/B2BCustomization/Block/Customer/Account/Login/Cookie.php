<?php

namespace Acidgreen\B2BCustomization\Block\Customer\Account\Login;

class Cookie extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    /**
     * @var boolean
     */
    protected $hasLoginRedirectCookie;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->redirect = $redirect;
        $this->urlEncoder = $urlEncoder;
        $this->customerSession = $customerSession;
        parent::__construct($context);
        // this has $this->_logger
    }

    public function getRefererUrl()
    {
        $refererUrl = $this->redirect->getRefererUrl();
        if ($this->customerSession->hasB2BCustomerAfterLoginUrl()) {
            $originalUrl = $this->customerSession->getB2BCustomerAfterLoginUrl();
            if (!preg_match("/logout/", $originalUrl)) {
                $refererUrl = $originalUrl;
            }
        }

        $refererUrl = $this->urlEncoder->encode($refererUrl);
        return $refererUrl;
    }

    public function isWebsiteRestrictionEnabled()
    {
        return !empty($this->_scopeConfig->getValue(
            'general/restriction/is_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->_storeManager->getWebsite()->getId()
        ));
    }
}
