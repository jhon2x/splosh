<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model\Rule\Action\Discount;

/**
 * Amasty Rule calculation by action.
 * @see \Amasty\Rules\Helper\Data::TYPE_EXPENCIVE
 */
class Themostexpencive extends Thecheapest
{
    const RULE_VERSION = '1.0.0';
    const DEFAULT_SORT_ORDER = 'desc';
}
