<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

use Magento\Framework\Session\SessionManager as CheckoutSession;
use Amasty\Rules\Model\DiscountRegistry as DiscountRegistry;
use Amasty\Rules\Model\ConfigModel as ConfigModel;

/**
 * LayoutProcessor for discount breakdown on checkout.
 */
class LayoutProcessor
{
    /**
     * @var DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigModel
     */
    private $configModel;

    public function __construct(
        DiscountRegistry $discountRegistry,
        CheckoutSession $checkoutSession,
        ConfigModel $configModel
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->configModel = $configModel;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        if ($this->configModel->getShowDiscountBreakdown()) {
            $this->discountRegistry->updateQuoteData($this->checkoutSession->getQuote());
            $rulesWithDiscount = $this->discountRegistry->getRulesWithAmount();
            $rulesWithDiscountArray = $this->discountRegistry->convertRulesWithDiscountToArray($rulesWithDiscount);

            $result['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']
            ['children']['discount-breakdown']['config'] = [
                'amount' => $rulesWithDiscountArray
            ];
        }

        return $result;
    }
}
