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



namespace Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;

use Mirasvit\EmailDesigner\Api\Data\ThemeInterface;
use Mirasvit\EmailDesigner\Controller\Adminhtml\Theme;

class Delete extends Theme
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $theme = $this->initModel();
            $this->themeRepository->delete($theme);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [
                ThemeInterface::ID => $this->getRequest()->getParam(ThemeInterface::ID)
            ]);
        }

        $this->messageManager->addSuccessMessage(__('Theme was successfully deleted'));
        return $resultRedirect->setPath('*/*/');
    }
}
