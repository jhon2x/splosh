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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Api\Repository\RepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Service\CloneServiceInterface;
use Mirasvit\Email\Controller\Adminhtml\Trigger;

class Move extends Trigger
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $move = (int) $this->getRequest()->getParam('move', 0);
            $model = $this->initModel();
            $isProcessed = false;

            try {
                foreach ($this->getRequest()->getParam('campaigns') as $campaignId) {
                    if ($campaignId !== $model->getCampaignId() || !$move) {
                        $this->moveTriggerTo($model, $campaignId);
                        $isProcessed = true;
                    }
                }

                if ($isProcessed) {
                    if ($move) {
                        $this->triggerRepository->delete($model);
                        $this->messageManager->addSuccessMessage(__('Trigger was successfully moved.'));
                    } else {
                        $this->messageManager->addSuccessMessage(__('Trigger was successfully copied.'));
                    }
                } else {
                    $this->messageManager->addNoticeMessage(
                        __('You cannot move the trigger to current campaign, use duplication instead.')
                    );
                }

                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find trigger to move'));

        return $resultRedirect->setPath('*/campaign/index');
    }

    /**
     * Move existing trigger to a specified campaign.
     *
     * @param TriggerInterface|AbstractModel $model
     * @param int                            $campaignId
     *
     * @return TriggerInterface
     */
    private function moveTriggerTo(TriggerInterface $model, $campaignId)
    {
        /** @var CloneServiceInterface $clonner */
        $clonner = $this->_objectManager->get(CloneServiceInterface::class);
        $triggerClone = $clonner->duplicate($model, $this->triggerRepository, [
            TriggerInterface::ID,
            TriggerInterface::CREATED_AT,
            TriggerInterface::UPDATED_AT
        ], [CampaignInterface::ID => $campaignId]);

        /** @var ChainRepositoryInterface|RepositoryInterface $chainRepository */
        $chainRepository = $this->_objectManager->get(ChainRepositoryInterface::class);
        /** @var AbstractModel $chain */
        foreach ($model->getChainCollection() as $chain) {
            $clonner->duplicate($chain, $chainRepository, [
                ChainInterface::ID,
                ChainInterface::CREATED_AT,
                ChainInterface::UPDATED_AT
            ], [TriggerInterface::ID => $triggerClone->getId()]);
        }

        return $triggerClone;
    }
}
