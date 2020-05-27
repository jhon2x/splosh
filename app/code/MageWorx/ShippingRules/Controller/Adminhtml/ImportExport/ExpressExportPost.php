<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class ExpressExportPost
 */
class ExpressExportPost extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_ShippingRules::import_export';

    /**
     * Menu id
     */
    const MENU_IDENTIFIER = 'MageWorx_ShippingRules::system_import_export';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \MageWorx\ShippingRules\Api\ExportHandlerInterfaceFactory
     */
    protected $exportHandlerFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \MageWorx\ShippingRules\Model\ImportExport\ExpressExportHandlerFactory $exportHandlerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \MageWorx\ShippingRules\Model\ImportExport\ExpressExportHandlerFactory $exportHandlerFactory
    ) {
        $this->fileFactory          = $fileFactory;
        $this->exportHandlerFactory = $exportHandlerFactory;
        parent::__construct($context);
    }

    /**
     * Export action from import/export shipping carriers, methods and rates
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \MageWorx\ShippingRules\Model\ImportExport\ExpressExportHandler $exportHandler */
        $exportHandler = $this->exportHandlerFactory->create();
        $content       = $exportHandler->getContent();

        return $this->fileFactory->create(
            'carriers_methods_rates_' . date('Y-m-d') . '_' . time() . '.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
