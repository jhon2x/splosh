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



namespace Mirasvit\Email\Controller\Adminhtml\Queue;

use Mirasvit\Email\Controller\Adminhtml\Queue;

class MassDelete extends Queue
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $removed = 0;
        foreach ($this->getRequest()->getParam('queue') as $id) {
            /** @var \Mirasvit\Email\Model\Queue $queue */
            $queue = $this->queueFactory->create()->load($id);
            $queue->delete();
            $removed++;
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been removed.', $removed)
        );

        return $resultRedirect->setPath('*/*/');
    }
}
