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


namespace Mirasvit\Email\Controller;

use Mirasvit\Email\Helper\Frontend;

abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Frontend
     */
    protected $frontendHelper;

    /**
     * @var Action\Context
     */
    protected $context;

    /**
     * Action constructor.
     *
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->frontendHelper = $context->getFrontendHelper();
    }

    /**
     * Return url @todo refactoring
     *
     * @param string $url
     * @param bool   $full
     * @return string
     */
    protected function _getUrl($url, $full = false)
    {
        $params = [];
        foreach ($this->getRequest()->getParams() as $key => $value) {
            if (strpos($key, 'utm_') !== false) {
                $params[$key] = $value;
            }
        }

        if ($full) {
            $url = $this->_url->getUrl($url, ['_query' => $params]);
        } else {
            $query = http_build_query($params);

            if ($query) {
                if (strpos($url, '?') !== false) {
                    $url .= '&' . $query;
                } else {
                    $url .= '?' . $query;
                }
            }
        }

        $hashPos  = strpos($url, '#');
        $paramPos = strpos($url, '?');
        // Place hash to the end of URL
        if ($hashPos !== false && $paramPos !== false && $paramPos > $hashPos) {
            $fragment = substr($url, $hashPos, $paramPos - $hashPos);
            $url = str_replace($fragment, '', $url).$fragment;
        }

        // tmp fix. Redirection allowed only within current domain name (current store base url)
        $currentHost = parse_url($this->_url->getBaseUrl(), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        if ($currentHost !== $urlHost) {
            $url = str_replace($urlHost, $currentHost, $url);
        }

        return $url;
    }
}
