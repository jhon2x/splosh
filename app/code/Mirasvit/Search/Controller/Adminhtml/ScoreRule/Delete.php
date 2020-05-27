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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Controller\Adminhtml\ScoreRule;

use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Controller\Adminhtml\ScoreRule;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;

class Delete extends ScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if ($id) {
            try {
                $rule = $this->scoreRuleRepository->get($id);
                $this->scoreRuleRepository->delete($rule);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->messageManager->addSuccessMessage(
                __('Rule was removed')
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select rule'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
