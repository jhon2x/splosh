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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Email\EmailDesigner\Variable\Php;

use Magento\Review\Model\ReviewFactory;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Context;
use Mirasvit\Email\Model\Config;

class Review
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ReviewFactory $reviewFactory,
        ProductFactory $productFactory,
        Context $context,
        Config $config
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * @return \Magento\Review\Model\Review
     */
    public function getReview()
    {
        $review = $this->reviewFactory->create();

        if ($this->context->getData('review')) {
            return $this->context->getData('review');
        } elseif ($this->context->getData('review_id')) {
            $review = $this->reviewFactory->create()
                ->load($this->context->getData('review_id'));
        }

        $this->context->setData('review', $review);

        return $review;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getReviewedProduct()
    {
        $product = $this->productFactory->create();

        if ($this->getReview()->getEntityPkValue()) {
            $product = $product->load($this->getReview()->getEntityPkValue());
        }

        return $product;
    }

    /**
     * Random variables
     *
     * @return array
     */
    public function getRandomVariables()
    {
        $variables = [];
        $reviewCollection = $this->reviewFactory->create()->getCollection();
        if ($reviewCollection->getSize()) {
            $reviewCollection->getSelect()->limit(1, rand(0, $reviewCollection->getSize() - 1));
            /** @var \Magento\Review\Model\Review $review */
            $review = $reviewCollection->getFirstItem();
            if ($review->getId()) {
                $variables['review_id'] = $review->getId();
                $variables['product_id'] = $review->getEntityPkValue();
            }
        }
        return $variables;
    }
}
