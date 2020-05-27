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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Filter;


use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\Image as ImageHelper;

class Image
{
    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Image constructor.
     *
     * @param ImageHelper    $imageHelper
     * @param ProductFactory $productFactory
     */
    public function __construct(ImageHelper $imageHelper, ProductFactory $productFactory)
    {
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
    }

    /**
     * Resize image
     *
     * @param string   $image  - path to image
     * @param string   $type   - image type
     * @param null|int $width  - width in pixels
     * @param null|int $height - height in pixels
     *
     * @return string
     */
    public function resize($image, $type = 'image', $width = null, $height = null)
    {
        switch ($type) {
            case 'small_image':
            case 'thumbnail':
                $type = 'product_' . $type;
                break;

            default:
                $type = 'product_base_image';
        }

        $url = $this->imageHelper->init($this->productFactory->create(), $type);

        if ($image) {
            $url->setImageFile($image);
        }

        if ($width) {
            $url->resize($width, $height);
        }

        return $image ? $url->getUrl() : $url->getDefaultPlaceholderUrl('image');
    }
}
