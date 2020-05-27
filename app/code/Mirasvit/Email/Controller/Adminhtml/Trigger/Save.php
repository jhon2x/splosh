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

use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Api\Data\TriggerInterface;
use Mirasvit\Email\Controller\Adminhtml\Trigger;

class Save extends Trigger
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getParams()) {
            $model = $this->initModel();

            try {
                $model->addData($this->filterData($data));
                $this->triggerRepository->save($model);

                $this->messageManager->addSuccessMessage(__('Trigger was successfully saved'));

                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/campaign/view', [CampaignInterface::ID => $model->getCampaignId()]);
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to find trigger to save'));

        return $resultRedirect->setPath('*/campaign/index');
    }

    /**
     * Filter request data.
     *
     * @param mixed[] $data
     *
     * @return array
     */
    private function filterData($data)
    {
        $filterRules = [];

        // Data is saved using the 'addData()' method, so if a cancellation event was deselect
        // - we override it using an empty array
        if (!isset($data[TriggerInterface::CANCELLATION_EVENT])) {
            $data[TriggerInterface::CANCELLATION_EVENT] = [];
        }

        if (!isset($data[TriggerInterface::TRIGGER_TYPE])) {
            $data[TriggerInterface::TRIGGER_TYPE] = '';
        }

        if (!$data[TriggerInterface::ID]) {
            $data[TriggerInterface::ID] = null;
        }

        // with_date param used in event and audience sections to ignore date conversion
        if ($this->getRequest()->getParam('with_date', 1)) {
            foreach ([TriggerInterface::ACTIVE_FROM, TriggerInterface::ACTIVE_TO] as $dateField) {
                if (!empty($data[$dateField])) {
                    $filterRules[$dateField] = $this->dateTimeFilter;
                } else {
                    /*
                     * For two attributes which represent datetime data in DB
                     * we should make converting such as:
                     * If they are empty we need to convert them into DB
                     * type NULL so in DB they will be empty and not some default value
                     */
                    $data[$dateField] = null;
                }
            }
        }

        return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
    }
}
