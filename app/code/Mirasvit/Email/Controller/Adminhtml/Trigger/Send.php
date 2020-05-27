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

use Mirasvit\Email\Controller\Adminhtml\Trigger;
use Magento\Framework\Controller\ResultFactory;

class Send extends Trigger
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($this->getRequest()->getParam('isAjax')) {
            if (!$this->getRequest()->getParam('email')) {
                $resultPage->setData(['success' => false, 'message' => __('Please specify email address')]);

                return $resultPage;
            }

            $model = $this->initModel();
            if ($model->getId()) {
                try {
                    $model->sendTest($this->getRequest()->getParam('email'));
                } catch (\Exception $e) {
                    return $resultPage->setData(['success' => false, 'message' => $e->getMessage()]);
                }

                return $resultPage->setData(['success' => true, 'message' => __('Test email was successfully sent')]);
            }
        }

        $resultPage->setData(['success' => false, 'message' => __('Unable to find trigger to send')]);

        return $resultPage;
    }
}
