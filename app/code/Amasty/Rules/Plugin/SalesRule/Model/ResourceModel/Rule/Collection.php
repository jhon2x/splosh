<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\SalesRule\Model\ResourceModel\Rule;

use \Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;

/**
 * Disabling sort order sorting if rules SetOf are enabled.
 *
 * @codingStandardsIgnoreFile
 */
class Collection
{
    /**
     * @param RuleCollection $subject
     * @param RuleCollection $result
     *
     * @return RuleCollection
     */
    public function afterSetValidationFilter(
        RuleCollection $subject,
        RuleCollection $result
    ) {
        /** @var RuleCollection $result */
        $result->unshiftOrder(
            'IF(simple_action = \'setof_fixed\' OR simple_action = \'setof_percent\', 1, 0)',
            \Magento\Framework\Data\Collection::SORT_ORDER_DESC
        );

        return $result;
    }
}
