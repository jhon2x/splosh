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



namespace Mirasvit\Search\Controller\Adminhtml\Synonym;

use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Controller\Adminhtml\Synonym;

class Save extends Synonym
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->initModel();

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This synonym no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setTerm($this->getRequest()->getParam(SynonymInterface::TERM))
                ->setSynonyms($this->getRequest()->getParam(SynonymInterface::SYNONYMS))
                ->setStoreId($this->getRequest()->getParam(SynonymInterface::STORE_ID));

            try {
                $this->synonymRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the synonym.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
