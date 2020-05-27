<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Component\ComponentRegistrar;
use MageWorx\ShippingRules\Controller\Adminhtml\ImportExport;

/**
 * Class ExpressExportExamplePost
 */
class ExpressExportExamplePost extends ImportExport
{
    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * ExpressExportExamplePost constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \MageWorx\ShippingRules\Api\ExportHandlerInterfaceFactory $exportHandlerFactory
     * @param \MageWorx\ShippingRules\Api\ImportHandlerInterfaceFactory $importHandlerFactory
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \MageWorx\ShippingRules\Api\ExportHandlerInterfaceFactory $exportHandlerFactory,
        \MageWorx\ShippingRules\Api\ImportHandlerInterfaceFactory $importHandlerFactory,
        ComponentRegistrar $componentRegistrar
    ) {
        parent::__construct($context, $fileFactory, $exportHandlerFactory, $importHandlerFactory);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Export example action from import/export shipping carriers, methods and rates
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $relativeFilePath = implode(
            DIRECTORY_SEPARATOR,
            [
                'examples',
                'example_export.csv'
            ]
        );
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'MageWorx_ShippingRules');
        $file = $path .
            DIRECTORY_SEPARATOR .
            $relativeFilePath;
        $content = file_get_contents($file);

        return $this->fileFactory->create(
            'shipping_suite_example.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
