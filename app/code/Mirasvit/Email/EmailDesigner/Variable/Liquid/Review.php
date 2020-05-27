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


namespace Mirasvit\Email\EmailDesigner\Variable\Liquid;

use Magento\Review\Model\ReviewFactory;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\AbstractVariable;
use Mirasvit\Email\Model\Config;

class Review extends AbstractVariable
{
    protected $supportedTypes = [\Magento\Review\Model\Review::class];

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ReviewFactory $reviewFactory,
        ProductFactory $productFactory,
        Config $config
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
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
     * Get review details
     *
     * @return string
     */
    public function getDetail()
    {
        return $this->getReview()->getData('detail');
    }

    /**
     * Get review title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getReview()->getData('title');
    }

    /**
     * Get customer nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->getReview()->getData('nickname');
    }
}
