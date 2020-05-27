<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model;

use Amasty\Rules\Api\Data\RuleInterface;

/**
 * Convert Special Promotions Rule data to Array
 */
class DataProviderPlugin
{
    /**
     * @param \Magento\SalesRule\Model\Rule\DataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(\Magento\SalesRule\Model\Rule\DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE])
                    && $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE] instanceof
                    RuleInterface
                ) {
                    $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE] =
                        $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE]->toArray();
                }
            }
        }

        return $result;
    }
}
