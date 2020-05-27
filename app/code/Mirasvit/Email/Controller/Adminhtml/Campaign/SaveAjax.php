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



namespace Mirasvit\Email\Controller\Adminhtml\Campaign;

use Mirasvit\Email\Api\Data\CampaignInterface;
use Mirasvit\Email\Controller\Adminhtml\Campaign;

class SaveAjax extends Campaign
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = [];
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        if ($data = $this->getRequest()->getParams()) {
            $model = $this->initModel();

            try {
                $model->setData($this->filterData($data));
                $this->campaignRepository->save($model);

                $result['message'] = __('Campaign was successfully saved');
                $result['error'] = false;
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
                $result['error'] = true;
            }
        }

        return $resultPage->setData($result);
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

        if (!$data[CampaignInterface::ID]) {
            $data[CampaignInterface::ID] = null;
        }

        foreach ([CampaignInterface::ACTIVE_FROM, CampaignInterface::ACTIVE_TO] as $dateField) {
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

        return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
    }
}
