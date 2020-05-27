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


namespace Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Mirasvit\Core\Api\ImageHelperInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Filters
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * Constructor
     *
     * @param TimezoneInterface $localeDate
     *
     * @internal param CheckoutHelper $checkoutHelper
     * @internal param ImageHelperInterface $imageHelper
     * @internal param Context $context
     */
    public function __construct(
        TimezoneInterface $localeDate,
        CheckoutHelper $checkoutHelper,
        ImageHelperInterface $imageHelper,
        Context $context
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->imageHelper = $imageHelper;
        $this->context = $context;
        $this->localeDate = $localeDate;
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->checkoutHelper->formatPrice($price);
    }

    /**
     * Retrieve formatting date.
     *
     * @param null $date
     * @param int  $format
     * @param bool $showTime
     * @param null $timezone
     *
     * @return string
     *
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Absolute image url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $type
     * @param int                            $width
     * @param int                            $height
     * @return string
     */
    public function getImageUrl($product, $type, $width = null, $height = null)
    {
        $product = $product->load($product->getId());

        $image = $this->imageHelper->init($product, $type, 'catalog/product');

        if ($width > 0) {
            $image->resize($width, $height);
        }

        //$img = str_replace('/pub/', '/', $image->__toString());

        return $image->__toString();
    }
}
