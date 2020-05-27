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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Email\EmailDesigner\Variable\Liquid;

use Magento\Framework\UrlInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\AbstractVariable;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Context;
use Mirasvit\Email\Model\Config;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Store as StoreVariable;

class Url extends AbstractVariable
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var StoreVariable
     */
    private $store;
    /**
     * @var StoreVariable
     */
    private $storeVariable;

    public function __construct(
        StoreVariable $storeVariable,
        UrlInterface $urlBuilder,
        Context $context,
        Config $config
    ) {
        parent::__construct();

        $this->urlBuilder = $urlBuilder;
        $this->context = $context;
        $this->config = $config;
        $this->storeVariable = $storeVariable;
    }

    /**
     * Get URL used to restore customer's shopping cart
     *
     * @return string
     */
    public function getRestoreCartUrl()
    {
        return $this->_getUrl('email/action/restoreCart');
    }

    /**
     * Get checkout URL
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->_getUrl('email/action/checkout');
    }

    /**
     * Get URL to view email in browser
     *
     * @return string
     */
    public function getViewInBrowserUrl()
    {
        return $this->_getUrl('email/action/view');
    }

    /**
     * Get unsubscribe URL from current trigger
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return $this->_getUrl('email/action/unsubscribe');
    }

    /**
     * Get unsubscribe URL from all triggers
     *
     * @return string
     */
    public function getUnsubscribeAllUrl()
    {
        return $this->_getUrl('email/action/unsubscribeAll');
    }

    /**
     * Get unsubscribe URL from all triggers and Magento newsletter
     *
     * @return string
     */
    public function getUnsubscribeNewsletterUrl()
    {
        return $this->_getUrl('email/action/unsubscribeNewsletter');
    }

    public function getFacebookUrl()
    {
        return $this->config->getFacebookUrl();
    }

    public function getTwitterUrl()
    {
        return $this->config->getTwitterUrl();
    }

    /**
     * @param string $route
     * @param array  $params
     * @return string
     */
    protected function _getUrl($route, $params = [])
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeVariable->setContext($this->context)->getStore();
        if ($this->context->getQueue() && $store) {
            $hash = $this->context->getQueue()->getUniqHash();

            $params = array_merge(['hash' => $hash], $params);

            return $store->getBaseUrl() . $route . '?' . http_build_query($params);
        } else {
            return __('This URL is not visible in preview.');
        }
    }
}
