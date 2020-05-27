<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Controller\Adminhtml\Shippingrules\Method;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\ShippingRules\Controller\Adminhtml\Shippingrules\Method as MethodParentController;

/**
 * Class Delete
 */
class Delete extends MethodParentController
{
    /**
     * Delete method action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model     = $this->methodRepository->getById($id);
                $carrierCode = $model->getCarrierCode();
                $this->methodRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the method'));
                if ($this->getRequest()->getParam(static::BACK_TO_PARAM) &&
                    $this->getRequest()->getParam(static::BACK_TO_PARAM == static::BACK_TO_CARRIER_PARAM)
                ) {
                    $this->_redirect(
                        'mageworx_shippingrules/shippingrules_carrier/edit',
                        ['carrier_code' => $carrierCode]
                    );

                    return;
                }
                $this->_redirect('mageworx_shippingrules/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the method right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('mageworx_shippingrules/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a method to delete.'));
        $this->_redirect('mageworx_shippingrules/*/');
    }
}
