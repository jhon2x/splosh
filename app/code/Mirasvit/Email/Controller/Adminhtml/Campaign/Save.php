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



namespace Mirasvit\Email\Controller\Adminhtml\Campaign;

use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Repository\CampaignTemplateRepositoryInterface;
use Mirasvit\Email\Controller\Adminhtml\Campaign;

class Save extends Campaign
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $back = $this->getRequest()->getParam('back');

        if ($data = $this->getRequest()->getParams()) {
            $model = $this->initModel();

            try {
                $model = $this->save($model, $data);

                if ($this->getRequest()->getParam(CampaignInterface::ID)) {
                    $this->messageManager->addSuccessMessage(__('Campaign was successfully saved'));
                }

                if ($back === 'edit') {
                    return $resultRedirect->setPath('*/*/view', [CampaignInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/view', [CampaignInterface::ID => $model->getId()]);
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find campaign to save'));

        return $resultRedirect->setPath('*/*/');
    }

    private function save(CampaignInterface $model, array $data)
    {
        if ($this->getRequest()->getParam('template_id')) {
            /** @var CampaignTemplateRepositoryInterface $templateRepository */
            $templateRepository = $this->_objectManager->get(CampaignTemplateRepositoryInterface::class);
            $model = $templateRepository->create($this->getRequest()->getParam('template_id'));
        } else {
            $model->addData($data);
            $this->campaignRepository->save($model);
        }

        return $model;
    }
}
