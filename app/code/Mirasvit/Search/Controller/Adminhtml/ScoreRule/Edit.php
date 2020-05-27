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

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Controller\Adminhtml\ScoreRule;

class Edit extends ScoreRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->initModel();
        $id = $this->getRequest()->getParam(ScoreRuleInterface::ID);

        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(
                $model->getId() ? __('Rule "%1"', $model->getTitle()) : __('New Rule')
            );

        return $resultPage;
    }
}
