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
 * @package   mirasvit/module-search-report
 * @version   1.0.5
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchReport\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Mirasvit\SearchReport\Api\Service\LogServiceInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class ResponsePlugin
{
    const COOKIE_NAME = 'searchReport-log';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var LogServiceInterface
     */
    private $logService;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        RequestInterface $request,
        LogServiceInterface $logService,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Registry $registry
    ) {
        $this->request = $request;
        $this->logService = $logService;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSendResponse(ResponseInterface $response)
    {
        /** @var \Magento\Framework\App\Response\Http $response */

        if ($this->request->getParam('q')) {
            $query = $this->request->getParam('q');
            $misspell = $this->request->getParam('o');
            $fallback = $this->request->getParam('f');
            $results = $this->registry->registry(SearchPlugin::REGISTRY_KEY);
            $source = $this->request->getFullActionName();

            if ($results === null) {
                return;
            }

            $log = $this->logService->logQuery($query, $results, $source, $misspell, $fallback);

            if ($log) {
                $this->setLogCookie($log->getId());
            }
        } else {
            $logId = $this->cookieManager->getCookie(self::COOKIE_NAME);
            $this->logService->logClick($logId);

            $this->setLogCookie(0);
        }
    }

    private function setLogCookie($logId)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata([
            'path' => '/',
        ]);

        /*
        * If enabled - allows subdomains
        */
        // $metadata->setDomain($_SERVER['HTTP_HOST']);
        $metadata->setSecure(isset($_SERVER['HTTPS']));
        $metadata->setHttpOnly(true);

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $logId, $metadata);
    }
}