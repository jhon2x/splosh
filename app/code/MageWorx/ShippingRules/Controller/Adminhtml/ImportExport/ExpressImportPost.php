<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Controller\Adminhtml\ImportExport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\ShippingRules\Model\ImportExport\ExpressImportHandler;

/**
 * Class ExpressImportPost
 */
class ExpressImportPost extends \Magento\Backend\App\Action
{
    /**
     * @var ExpressImportHandler
     */
    private $expressImportHandlerFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * ExpressImportPost constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ExpressImportHandler $expressImportHandlerFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ExpressImportHandler $expressImportHandlerFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->expressImportHandlerFactory = $expressImportHandlerFactory;
    }

    /**
     * Import action from import/export shipping methods, carriers and rates
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $this->expressImportHandlerFactory->importFromCsvFile(
                    $this->getRequest()->getFiles('import_carriers_file')
                );

                $this->messageManager->addSuccessMessage(__('Data has been imported.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
