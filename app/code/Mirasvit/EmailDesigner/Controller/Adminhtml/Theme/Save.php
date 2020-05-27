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

class Save extends Theme
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(ThemeInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This theme no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
            try {
                $model->addData($data);
                if (isset($data[ThemeInterface::THEME_AREAS])) {
                    foreach ($data[ThemeInterface::THEME_AREAS] as $key => $value) {
                        $model->setDataUsingMethod($key, $value);
                    }
                }

                $this->themeRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the theme.'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [ThemeInterface::ID => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [
                    ThemeInterface::ID => $this->getRequest()->getParam(ThemeInterface::ID)
                ]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
