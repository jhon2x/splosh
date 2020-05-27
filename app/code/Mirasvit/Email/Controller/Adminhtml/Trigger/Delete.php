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


namespace Mirasvit\Email\Controller\Adminhtml\Trigger;

use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Controller\Adminhtml\Trigger;

class Delete extends Trigger
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($model->getId()) {
            try {
                $this->triggerRepository->delete($model);

                $this->messageManager->addSuccessMessage(__('The trigger has been deleted.'));

                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('This trigger no longer exists.'));

            return $resultRedirect->setPath('*/campaign/index');
        }
    }
}
