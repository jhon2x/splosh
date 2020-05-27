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


use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\StoreManagerInterface;

class Price
{
    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;
    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Price constructor.
     *
     * @param DirectoryHelper       $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param CheckoutHelper        $checkoutHelper
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        StoreManagerInterface $storeManager,
        CheckoutHelper $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Format price.
     *
     * @param float $price
     *
     * @return string
     */
    public function format_price($price)
    {
        return $this->checkoutHelper->formatPrice($price);
    }

    /**
     * Convert
     *
     * Convert price from base store currency to 'x' currency.
     *
     * @param string $input
     * @param string $toCurrency
     * @return string
     */
    public function convert($input, $toCurrency)
    {
        $value = floatval($input);

        return $this->directoryHelper->currencyConvert(
            $value,
            $this->storeManager->getStore()->getBaseCurrencyCode(),
            $toCurrency
        );
    }
}
