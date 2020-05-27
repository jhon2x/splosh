<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Splosh\ProductLabels\Controller\Adminhtml\Action;

use Splosh\ProductLabels\Model\LabelFactory;
use Splosh\ProductLabels\Helper\Data;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Backend\App\Action;

/**
 * Class ImageUpload
 */
class Upload extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LabelFactory
     */
    protected $labelFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Upload constructor.
     * @param Action\Context $context
     * @param LabelFactory $labelFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        LabelFactory $labelFactory,
        RawFactory $resultRawFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->labelFactory = $labelFactory;
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var Uploader $uploader */
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);

            $result = $uploader->save($mediaDirectory->getAbsolutePath($this->helper->getBaseMediaPath()));
            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->helper->getMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}
