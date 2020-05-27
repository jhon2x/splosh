<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

/**
 * Add Amasty Rule actions to select.
 */
class ValueProvider
{
    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    public function __construct(
        \Amasty\Rules\Helper\Data $rulesDataHelper
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
    }

    public function afterGetMetadataValues(
        SalesRuleValueProvider $subject,
        $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        foreach ($actions as &$action) {
            if ($action['value'] == \Magento\SalesRule\Model\Rule::BUY_X_GET_Y_ACTION) {
                $action['label'] = __("Buy N products, and get next products with discount");
                break;
            }
        }
        $actions = array_merge($actions, $this->rulesDataHelper->getDiscountTypes());

        return $result;
    }
}
