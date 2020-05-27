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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Controller\Track;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Mirasvit\EmailReport\Api\Repository\ClickRepositoryInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;

class Click extends Action
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var ClickRepositoryInterface
     */
    private $clickRepository;

    /**
     * @var StorageServiceInterface
     */
    private $storageService;

    public function __construct(
        ClickRepositoryInterface $clickRepository,
        SessionManagerInterface $sessionManager,
        StorageServiceInterface $storageService,
        Context $context
    ) {
        $this->clickRepository = $clickRepository;
        $this->sessionManager = $sessionManager;
        $this->storageService = $storageService;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $redirectTo = base64_decode(strtr($this->getRequest()->getParam('url'), '-_', '+/'));

        if ($uniqueHash = $this->getRequest()->getParam(StorageServiceInterface::QUEUE_PARAM_NAME)) {
            $queue = $this->storageService->retrieveQueue($uniqueHash);

            if ($queue) {
                $open = $this->clickRepository->create()
                    ->setTriggerId($queue->getTriggerId())
                    ->setQueueId($queue->getId())
                    ->setSessionId($this->sessionManager->getSessionId());

                $this->clickRepository->ensure($open);
                $this->storageService->persistQueueHash($queue->getUniqHash());
            }
        }

        $redirectTo = $this->prepareUrlParams($redirectTo);

        return $this->getResponse()->setRedirect($redirectTo);
    }

    public function prepareUrlParams($link)
    {
        $params = $this->getRequest()->getParams();
        unset($params['url']);
        foreach ($params as $param => $value) {
            $params[] .= $param . '=' . $value;
            unset($params[$param]);
        }

        $params = implode('&', $params);
        $components = parse_url($link);
        $newLink = false;
        if (isset($components['path']) && isset($components['host'])) {
            if (isset($components['query'])) {
                $newLink = $link . '&' . $params;
            } else {
                $newLink = $link . '?' . $params;
            }
        }

        if (isset($components['fragment'])) {
            $newLink = str_replace('#' . $components['fragment'], '', $newLink) . '#' . $components['fragment'];
        }

        return $newLink;
    }
}
