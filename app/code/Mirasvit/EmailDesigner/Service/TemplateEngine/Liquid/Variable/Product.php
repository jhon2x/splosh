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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\Core\Api\ImageHelperInterface;

class Product extends AbstractVariable
{
    protected $supportedTypes = [
        'Magento\Catalog\Model\Product'
    ];

    protected $whitelist = [
        'getName',
        'getPrice',
        'getProductUrl',
    ];

    /**
     * @var ImageHelperInterface
     */
    private $imageHelper;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(
        ProductFactory $productFactory,
        ImageHelperInterface $imageHelper
    ) {
        parent::__construct();

        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->context->getData('product');
        if ($product) {
            return $product;
        }

        if (!$product && ($item = $this->context->getData('item'))) {
            $product = $item->getProduct();
        }

        if (!$product && ($productId = $this->context->getData('product_id'))) {
            $product = $this->productFactory->create()->load($productId);
        }

        if ($product) {
            $this->context->setData('product', $product);
        }

        return $product;
    }

    /** VARIABLES **/

    /**
     * Get product image url
     *
     * @filter | resize: "image", 300
     *
     * @param ProductModel $product
     *
     * @return string
     */
    public function getImage(\Magento\Catalog\Model\Product $product = null)
    {
        $image   = '';
        if ($product || ($product = $this->getProduct())) {
            if (!$image = $product->getImage()) {
                $image = $product->getSmallImage();
            }
        }

        return $image;
    }
}
