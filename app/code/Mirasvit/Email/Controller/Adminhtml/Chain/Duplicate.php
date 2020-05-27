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
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\Email\Api\Repository\RepositoryInterface;
use Mirasvit\Email\Api\Repository\Trigger\ChainRepositoryInterface;
use Mirasvit\Email\Api\Service\CloneServiceInterface;

class Duplicate extends Action
{
    /**
     * Authorization level of a basic admin session for current page.
     */
    const ADMIN_RESOURCE = 'Mirasvit_Email::campaign';
    /**
     * @var CloneServiceInterface
     */
    private $clonner;
    /**
     * @var ChainRepositoryInterface|RepositoryInterface
     */
    private $chainRepository;

    public function __construct(
        CloneServiceInterface $clonner,
        ChainRepositoryInterface $chainRepository,
        Context $context
    ) {
        $this->clonner = $clonner;

        parent::__construct($context);
        $this->chainRepository = $chainRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $campaignId = $this->getRequest()->getParam(CampaignInterface::ID);
            $model = $this->chainRepository->get($this->getRequest()->getParam(ChainInterface::ID));

            try {
                $this->duplicate($model);

                $this->messageManager->addSuccessMessage(__('Email was successfully duplicated.'));

                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $campaignId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $campaignId]);
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find email to duplicate'));

        return $resultRedirect->setPath('*/campaign/index');
    }

    /**
     * @param ChainInterface|AbstractModel $chain
     *
     * @return ChainInterface
     */
    private function duplicate(ChainInterface $chain)
    {
        $chainClone = $this->clonner->duplicate($chain, $this->chainRepository, [
            ChainInterface::ID,
            ChainInterface::CREATED_AT,
            ChainInterface::UPDATED_AT
        ]);

        return $chainClone;
    }
}
