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



namespace Mirasvit\Search\Controller\Adminhtml\Index;

use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Controller\Adminhtml\Index as ParentIndex;

class Save extends ParentIndex
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParams()) {
            $index = $this->initModel();

            if (!$index->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This search index no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $index->setTitle($this->getRequest()->getParam(IndexInterface::TITLE))
                ->setIdentifier($this->getRequest()->getParam(IndexInterface::IDENTIFIER))
                ->setIsActive($this->getRequest()->getParam(IndexInterface::IS_ACTIVE))
                ->setPosition($this->getRequest()->getParam(IndexInterface::POSITION))
                ->setAttributes($this->getRequest()->getParam('attributes'))
                ->setProperties($this->getRequest()->getParam('properties'));

            try {
                $this->indexRepository->save($index);

                $this->messageManager->addSuccessMessage(__('You saved the search index.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $index->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [IndexInterface::ID => $id]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}
