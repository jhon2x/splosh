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

use Mirasvit\Email\Controller\Action;
use Mirasvit\Email\Model\Unsubscription;

class UnsubscribeAll extends Action
{
    /**
     * @var Unsubscription
     */
    private $unsubscription;

    /**
     * UnsubscribeAll constructor.
     *
     * @param Unsubscription $unsubscription
     * @param Context        $context
     */
    public function __construct(
        Unsubscription $unsubscription,
        Context $context
    ) {
        parent::__construct($context);

        $this->unsubscription = $unsubscription;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($hash = $this->getRequest()->getParam('hash')) {
            $queue = $this->frontendHelper->getQueue($hash);

            if (!$queue) {
                $this->messageManager->addErrorMessage(__('Wrong unsubscription link'));
                $this->getResponse()->setRedirect($this->_getUrl('/', true));

                return;
            }

            $this->unsubscription->unsubscribe($queue->getRecipientEmail());

            $this->messageManager
                ->addSuccessMessage(__('You have been successfully unsubscribed from receiving these emails.'));
        }

        $this->getResponse()->setRedirect($this->_getUrl('/', true));
    }
}
