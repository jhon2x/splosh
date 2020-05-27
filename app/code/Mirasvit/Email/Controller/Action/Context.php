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



namespace Mirasvit\Email\Controller\Action;

use Magento\Framework\Controller\ResultFactory;

class Context extends \Magento\Framework\App\Action\Context
{
    /**
     * @var \Mirasvit\Email\Helper\Frontend
     */
    private $frontendHelper;

    /**
     * @param \Mirasvit\Email\Helper\Frontend                      $frontendHelper
     * @param \Magento\Framework\App\RequestInterface              $request
     * @param \Magento\Framework\App\ResponseInterface             $response
     * @param \Magento\Framework\ObjectManagerInterface            $objectManager
     * @param \Magento\Framework\Event\ManagerInterface            $eventManager
     * @param \Magento\Framework\UrlInterface                      $url
     * @param \Magento\Framework\App\Response\RedirectInterface    $redirect
     * @param \Magento\Framework\App\ActionFlag                    $actionFlag
     * @param \Magento\Framework\App\ViewInterface                 $view
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Controller\ResultFactory          $resultFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Email\Helper\Frontend $frontendHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        ResultFactory $resultFactory
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory
        );

        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @return \Mirasvit\Email\Helper\Frontend
     */
    public function getFrontendHelper()
    {
        return $this->frontendHelper;
    }
}
