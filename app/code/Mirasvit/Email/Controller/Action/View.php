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
use Mirasvit\Email\Model\Queue;
use Magento\Framework\Controller\ResultFactory;

class View extends Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();

        if ($hash = $this->getRequest()->getParam('hash')) {
            $queue = $this->frontendHelper->getQueue($hash);

            if (!$queue) {
                $this->messageManager->addErrorMessage(__('The email not found.'));

                return $resultRedirect->setPath('/');
            }

            return $response->setBody($queue->getContent());
        } else {
            $this->messageManager->addErrorMessage(__('The email not found.'));

            return $resultRedirect->setPath('/');
        }
    }
}
