<?php

namespace Splosh\ProductLabels\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Catalog\Model\Product;
use Splosh\ProductLabels\Model\LabelFactory;
use Splosh\ProductLabels\Model\Label;

/**
 * Class Data
 * @package Splosh\ProductLabels\Helper
 */
class Data extends AbstractHelper
{

    const IMAGE_MEDIA_PATH              = 'splosh/product_labels';
    const IMAGE_TYPE_FORM_PREVIEW       = 'preview_in_form';
    const IMAGE_TYPE_THUMBNAIL          = 'thumbnail_image';
    const IMAGE_TYPE_FRONTEND_PREVIEW   = 'preview_frontend';

    /**
     * Position Values
     */
    const POSITION_OPTION_TOP_LEFT      = 'top_left';
    const POSITION_OPTION_TOP_CENTER    = 'top_center';
    const POSITION_OPTION_TOP_RIGHT     = 'top_right';
    const POSITION_OPTION_MIDDLE_LEFT   = 'middle_left';
    const POSITION_OPTION_MIDDLE_RIGHT  = 'middle_right';
    const POSITION_OPTION_BOTTOM_LEFT   = 'bottom_left';
    const POSITION_OPTION_BOTTOM_CENTER = 'bottom_center';
    const POSITION_OPTION_BOTTOM_RIGHT  = 'bottom_right';

    /**
     * Admin Url Path
     */
    const URL_PATH_SAVE     = 'splosh_product_labels/action/save';
    const URL_PATH_DELETE   = 'splosh_product_labels/action/delete';
    const URL_PATH_EDIT     = 'splosh_product_labels/action/edit';
    const URL_PATH_UPLOAD   = 'splosh_product_labels/action/upload';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var Label
     */
    protected $labelFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     * @param UrlInterface $urlBuilder
     * @param ImageFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     * @param LabelFactory $labelFactory
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        ImageFactory $imageFactory,
        StoreManagerInterface $storeManager,
        LabelFactory $labelFactory
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->labelFactory = $labelFactory->create();
    }

    /**
     * @param $path
     * @param null $type
     * @param int $height
     * @param int $width
     * @return string
     */
    public function getImageUrl($path, $type = null, $height = 300, $width = 300)
    {
        if (!$path) return '';

        if ($type !== null) {
            $attributes = $this->getAttributesByType($type);
            $height = !empty($attributes['height']) ? $attributes['height'] : $height;
            $width = !empty($attributes['width']) ? $attributes['width'] : $width;
        }

        $filePath = $this->getMediaPath($path);
        $pathArray = explode('/', $filePath);
        $fileName = array_pop($pathArray);
        $directoryPath = implode('/', $pathArray);
        $imagePath = $directoryPath . '/' . $width . 'x' . $height . '/';

        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imgAbsolutePath = $mediaDirectory->getAbsolutePath($imagePath);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($filePath);

        $imgFilePath = $imgAbsolutePath . $fileName;
        if (!file_exists($imgFilePath)) {
            $this->createImageFile($fileAbsolutePath, $imgAbsolutePath, $fileName, $width, $height);
        }

        return $this->getUrl($imagePath . $fileName);
    }

    /**
     * @param $type
     * @return array
     */
    private function getAttributesByType($type)
    {
        $data = [];
        switch ($type) {
            case static::IMAGE_TYPE_THUMBNAIL:
                $data['width']  = 75;
                $data['height'] = 75;
                break;
            case static::IMAGE_TYPE_FORM_PREVIEW:
                $data['width']  = 116;
                $data['height'] = 148;
                break;
            case static::IMAGE_TYPE_FRONTEND_PREVIEW:
                $data['width']  = 150;
                $data['height'] = 150;
                break;
            default:
                $data['width']  = 300;
                $data['height'] = 300;
                break;
        }

        return $data;
    }

    public function getImageOrigSize($image)
    {
        $fullPathToImage  = $this->getMediaPath($image);
        $mediaDirectory   = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($fullPathToImage);
        if (file_exists($fileAbsolutePath)) {
            $fileSize = @filesize($fileAbsolutePath);
        } else {
            return 0;
        }

        return $fileSize;
    }

    /**
     * @param $file
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->prepareFile($file);
    }

    /**
     * @param $file
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * @param $origFilePath
     * @param $imagePath
     * @param $newFileName
     * @param $width
     * @param $height
     */
    private function createImageFile($origFilePath, $imagePath, $newFileName, $width, $height)
    {
        try {
            $image = $this->imageFactory->create($origFilePath);
            $image->keepAspectRatio(true);
            $image->keepFrame(true);
            $image->keepTransparency(true);
            $image->constrainOnly(false);
            $image->backgroundColor([255, 255, 255]);
            $image->quality(100);
            $image->resize($width, $height);
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);
            $image->save($imagePath, $newFileName);
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    /**
     * @param $file
     * @return string
     */
    public function getUrl($file)
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($this->prepareFile($file), '/');
    }

    /**
     * @param $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getMediaBaseUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @return bool
     */
    public function getBaseUrl()
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA
                );
        } catch (\Exception $e) {
            $this->_logger->error($e->__toString());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getMediaBaseUrl()
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $this->getBaseMediaPath();
        } catch (\Exception $e) {
            $this->_logger->error($e->__toString());
            return false;
        }
    }

    /**
     * @return string
     */
    public function getBaseMediaPath()
    {
        return static::IMAGE_MEDIA_PATH;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProductLabel(Product $product)
    {
        $productLabelId = $product->getProductLabel();
        $productLabel = $this->labelFactory->load($productLabelId);

        $imageUrl = $this->getMediaBaseUrl() . $productLabel->getImage();
        $imageTitle = __($productLabel->getName());

        return ['image_url' => $imageUrl, 'image_title' => $imageTitle];
    }
}