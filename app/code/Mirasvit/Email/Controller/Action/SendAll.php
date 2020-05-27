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

use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Email\Controller\Action;
use Mirasvit\Email\Service\FrontSessionInitiator;

class SendAll extends Action
{
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;
    /**
     * @var FrontSessionInitiator
     */
    private $emailSessionManager;

    /**
     * Send constructor.
     *
     * @param FrontSessionInitiator    $emailSessionManager
     * @param QueueRepositoryInterface $queueRepository
     * @param Context                  $context
     */
    public function __construct(
        FrontSessionInitiator $emailSessionManager,
        QueueRepositoryInterface $queueRepository,
        Context $context
    ) {
        parent::__construct($context);

        $this->emailSessionManager = $emailSessionManager;
        $this->queueRepository = $queueRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('form_key', null) !== $this->emailSessionManager->get()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $this->messageManager->addErrorMessage(__('Operation is not allowed.'));

            return $resultRedirect->setPath('/');
        }

        $sent = 0;
        foreach (explode(',', $this->getRequest()->getParam('queue')) as $id) {
            $queue = $this->queueRepository->get($id);
            if ($queue) {
                $queue->send(true);
                $sent++;
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been sent.', $sent)
        );

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
