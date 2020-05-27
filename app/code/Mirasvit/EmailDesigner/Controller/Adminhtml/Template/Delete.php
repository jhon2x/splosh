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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Controller\Adminhtml\Template;

use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;
use Mirasvit\EmailDesigner\Controller\Adminhtml\Template;

class Delete extends Template
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $template = $this->initModel();
            if ($template->isSystem()) {
                $this->messageManager->addErrorMessage(__('Sorry, but you cannot remove the default template.'));
                return $resultRedirect->setPath('*/*/edit', [
                    TemplateInterface::ID => $this->getRequest()->getParam(TemplateInterface::ID)
                ]);
            }
            $this->templateRepository->delete($template);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', [
                TemplateInterface::ID => $this->getRequest()->getParam(TemplateInterface::ID)
            ]);
        }

        $this->messageManager->addSuccessMessage(__('Template was successfully deleted'));
        return $resultRedirect->setPath('*/*/');
    }
}
