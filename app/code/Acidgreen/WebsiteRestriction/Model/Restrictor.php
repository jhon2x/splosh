<?php

namespace Acidgreen\WebsiteRestriction\Model;

use Magento\Customer\Model\Url;

class Restrictor extends \Magento\WebsiteRestriction\Model\Restrictor
{

    /**
     * Restrict access to website
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param bool $isCustomerLoggedIn
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function restrict($request, $response, $isCustomerLoggedIn)
    {
        $logger = new \Zend\Log\Logger;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SPL-430-Acidgreen-WebsiteRestriction-Model-Restrictor.log');
        $logger->addWriter($writer);

        switch ($this->_config->getMode()) {
            // show only landing page with 503 or 200 code
            case \Magento\WebsiteRestriction\Model\Mode::ALLOW_NONE:
                if ($request->getFullActionName() !== 'restriction_index_stub') {
                    $request->setModuleName(
                        'restriction'
                    )->setControllerName(
                        'index'
                    )->setActionName(
                        'stub'
                    )->setDispatched(
                        false
                    );
                    return;
                }
                $httpStatus = $this->_config->getHTTPStatusCode();
                if (\Magento\WebsiteRestriction\Model\Mode::HTTP_503 === $httpStatus) {
                    $response->setStatusHeader(503, '1.1', 'Service Unavailable');
                }
                break;

            case \Magento\WebsiteRestriction\Model\Mode::ALLOW_REGISTER:
                // break intentionally omitted

                //redirect to landing page/login
            case \Magento\WebsiteRestriction\Model\Mode::ALLOW_LOGIN:
                if (!$isCustomerLoggedIn && !$this->_customerSession->isLoggedIn()) {
                    // see whether redirect is required and where
                    $redirectUrl = false;
                    $allowedActionNames = array_map('strtolower', $this->_config->getGenericActions());
                    if ($this->registration->isAllowed()) {
                        $allowedActionNames = array_merge($allowedActionNames, $this->_config->getRegisterActions());
                    }

                    // to specified landing page
                    $restrictionRedirectCode = $this->_config->getHTTPRedirectCode();
                    if (\Magento\WebsiteRestriction\Model\Mode::HTTP_302_LANDING === $restrictionRedirectCode) {
                        $cmsPageViewAction = 'cms_page_view';
                        $allowedActionNames[] = $cmsPageViewAction;
                        $pageIdentifier = $this->_config->getLandingPageCode();
                        // Restrict access to CMS pages too
                        if (!in_array($request->getFullActionName(), $allowedActionNames)
                            || $request->getFullActionName() === $cmsPageViewAction
                            && $request->getAlias('rewrite_request_path') !== $pageIdentifier
                        ) {
                            $redirectUrl = $this->_urlFactory->create()->getUrl('', ['_direct' => $pageIdentifier]);
                        }
                    } elseif (!in_array(strtolower($request->getFullActionName()), $allowedActionNames)) {
                        // to login form
                        $redirectUrl = $this->_urlFactory->create()->getUrl('customer/account/login');
                    }

                    if ($redirectUrl) {
                        $response->setRedirect($redirectUrl);
                        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                    }
                    $redirectToDashboard = $this->_scopeConfig->isSetFlag(
                        Url::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    if ($redirectToDashboard) {
                        $afterLoginUrl = $this->customerUrl->getDashboardUrl();
                    } else {
                        $afterLoginUrl = $this->_urlFactory->create()->getUrl();
                    }

                    /** SPL-430 */
                    $originalUrl = $this->_urlFactory->create()->getUrl(
                        str_replace('_', '/', $request->getFullActionName()),
                        $request->getParams()
                    );
                    if (!empty($request->getParam('referer'))) {
                        $originalUrl = $this->getRefererUrlDecoded($request);
                    }

                    // @todo: friendly URLs
                    if ($originalUrl != $afterLoginUrl
                        && !preg_match('/(login|logout)/', $request->getFullActionName())) {
                        // && preg_match('/(catalog|checkout)/', $request->getFullActionName())) {
                        // $this->_customerSession->setB2BCustomerAfterLoginUrl($originalUrl);
                        // redirect to friendly URL instead?
                        $originalUrl = rtrim($this->_urlFactory->create()->getUrl(), '/') . $request->getServer()->get('REQUEST_URI');
                        $this->_customerSession->setB2BCustomerAfterLoginUrl($originalUrl);

                        $this->_customerSession->setWebsiteRestrictionOriginalUrl($originalUrl);
                        $afterLoginUrl = $originalUrl;
                    }

                    $this->_session->setWebsiteRestrictionAfterLoginUrl($afterLoginUrl);

                } elseif ($this->_session->hasWebsiteRestrictionAfterLoginUrl()) {
                    /** SPL-430 */

                    if ($this->_customerSession->hasB2BCustomerAfterLoginUrl() && $this->_customerSession->isLoggedIn()) {
                        $this->_session->unsWebsiteRestrictionAfterLoginUrl();
                        // $response->setRedirect($_SESSION['b2b_customer_after_login_url']);
                        $logger->debug('SPL-430 :: Restrictor :: we can use the new getB2BCustomerAfterLoginUrl now? :: '.print_r([
                            $this->_customerSession->getB2BCustomerAfterLoginUrl(),
                        ], true));
                        $response->setRedirect($this->_customerSession->getB2BCustomerAfterLoginUrl());
                        $this->_customerSession->unsB2BCustomerAfterLoginUrl();
                    } else {
                        $response->setRedirect($this->_session->getWebsiteRestrictionAfterLoginUrl(true)); // original
                    }
                    $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true); // original
                }
                break;
        }
    }

    protected function getRefererUrlDecoded($request)
    {
        $urlDecoder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Url\Decoder');
        if (!empty($request->getParam('referer'))) {
            return $urlDecoder->decode($request->getParam('referer'));
        }
        return '';
    }
}
