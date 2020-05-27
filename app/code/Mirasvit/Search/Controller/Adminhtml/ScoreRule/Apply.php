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

use Magento\Framework\App\ObjectManager;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\ScoreRule;
use Mirasvit\Search\Model\ScoreRule\Indexer\ScoreRuleIndexer;

class Apply extends ScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->initModel();

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $objectManager = ObjectManager::getInstance();

            $scoreRuleIndexer = $objectManager->create(ScoreRuleIndexer::class);
            $scoreRuleIndexer->execute($model, []);

            $this->messageManager->addSuccessMessage(__('You applied the rule.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [ScoreRuleInterface::ID => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }
    }
}
