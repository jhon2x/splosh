<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template\Context as Context;
use Amasty\Rules\Model\DiscountRegistry as DiscountRegistry;
use Amasty\Rules\Model\Debug as DebugModel;

/**
 * Add debug data to cart item information.
 */
class Debug extends Generic
{
    /**
     * @var DiscountRegistry
     */
    private $discountRegistry;

    /**
     * @var DebugModel
     */
    private $debugModel;

    public function __construct(
        Context $context,
        DiscountRegistry $discountRegistry,
        DebugModel $debugModel,
        array $data = []
    ) {
        $this->discountRegistry = $discountRegistry;
        $this->debugModel = $debugModel;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|array
     */
    public function getDiscountDebuggerInformation()
    {
        if (!$this->debugModel->isDebugDisplayAllowed()) {
            return false;
        }

        $quoteId = $this->getItem()->getId();
        $discountInfo = $this->discountRegistry->getDiscountDataForDebugger();

        if (!$discountInfo || !isset($discountInfo[$quoteId])) {
            return false;
        }

        return $discountInfo[$quoteId];
    }
}
