<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Controller\Adminhtml\Shippingrules\Rate;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 */
class Delete extends \MageWorx\ShippingRules\Controller\Adminhtml\Shippingrules\Rate
{
    /**
     * Delete rate action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find a rate to delete.'));
            $this->_redirect('mageworx_shippingrules/*/');
        }

        try {
            /** @var \MageWorx\ShippingRules\Model\Carrier\Method\Rate $model */
            $model      = $this->rateRepository->getById($id);
            $name       = $model->getTitle();
            $methodCode = $model->getMethodCode();
            $this->rateRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('You deleted the rate %1.', [$name]));
            if ($this->isBackToMethod()) {
                $this->_redirect(
                    'mageworx_shippingrules/shippingrules_method/edit',
                    ['code' => $methodCode]
                );

                return;
            }
            $this->_redirect('mageworx_shippingrules/*/index');

            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the rate right now. Please review the log and try again.')
            );
            $this->logger->critical($e);
            $this->_redirect('mageworx_shippingrules/*/edit', ['id' => $this->getRequest()->getParam('id')]);

            return;
        }
    }
}
