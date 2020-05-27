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


namespace Mirasvit\Email\Controller\Adminhtml\Chain;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session for current page.
     */
    const ADMIN_RESOURCE = 'Mirasvit_Email::campaign';

    public function __construct(
        ChainRepositoryInterface $chainRepository,
        Context $context
    ) {
        $this->chainRepository = $chainRepository;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $campaignId = $this->getRequest()->getParam(CampaignInterface::ID);
        $model = $this->chainRepository->get($this->getRequest()->getParam(ChainInterface::ID));

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($model->getId()) {
            try {
                $this->chainRepository->delete($model);

                $this->messageManager->addSuccessMessage(__('The email has been deleted.'));

                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $campaignId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $campaignId]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('This email no longer exists.'));

            return $resultRedirect->setPath('*/campaign/index');
        }
    }
}
