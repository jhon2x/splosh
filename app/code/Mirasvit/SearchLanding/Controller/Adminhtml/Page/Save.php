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
 * @package   mirasvit/module-search-landing
 * @version   1.0.7
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchLanding\Controller\Adminhtml\Page;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\SearchLanding\Api\Data\PageInterface;
use Mirasvit\SearchLanding\Controller\Adminhtml\Page;

class Save extends Page
{

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(PageInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->filter($this->getRequest()->getParams());

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setQueryText($data[PageInterface::QUERY_TEXT])
                ->setUrlKey($data[PageInterface::URL_KEY])
                ->setTitle($data[PageInterface::TITLE])
                ->setMetaKeywords($data[PageInterface::META_KEYWORDS])
                ->setMetaDescription($data[PageInterface::META_DESCRIPTION])
                ->setLayoutUpdate($data[PageInterface::LAYOUT_UPDATE])
                ->setStoreIds($data[PageInterface::STORE_IDS])
                ->setIsActive($data[PageInterface::IS_ACTIVE]);

            try {
                $this->pageRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the page.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [PageInterface::ID => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath(
                    '*/*/edit',
                    [PageInterface::ID => $this->getRequest()->getParam(PageInterface::ID)]
                );
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * @param array $rawData
     * @return array
     */
    private function filter(array $rawData)
    {
        return $rawData;
    }
}
