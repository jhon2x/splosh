<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Model;

/**
 * System config provider.
 */
class ConfigModel
{
    /**
     * module name
     */
    const MODULE = 'amrules';

    /**#@+
     * Constants defined for section names
     */
    const SECTION_GENERAL = 'general';
    const SECTION_SKIP_PRICE = 'skip_price';
    const SECTION_DISCOUNT_BREAKDOWN = 'discount_breakdown';
    const SECTION_DEBUG = 'discount_debug';
    /**#@-*/

    /**#@+
     * Constants defined for field names
     */
    const FIELD_SKIP_SPECIAL_PRICE = 'skip_special_price';
    const FIELD_SKIP_SPECIAL_PRICE_CONFIGURABLE = 'skip_special_price_configurable';
    const FIELD_SKIP_TIER_PRICE = 'skip_tier_price';
    const FIELD_OPTIONS_VALUE = 'options_value';
    const FIELD_DISCOUNT_BREAKDOWN_ENABLE = 'enable';
    const FIELD_DEBUG_ENABLE = 'enable_debug';
    const FIELD_ALLOWED_IP = 'allowed_ip';
    /**#@-*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getOptionsValue()
    {
        return $this->getConfigValue(self::SECTION_GENERAL, self::FIELD_OPTIONS_VALUE);
    }

    /**
     * @return mixed
     */
    public function getSkipSpecialPrice()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_SPECIAL_PRICE);
    }

    /**
     * @return mixed
     */
    public function getSkipTierPrice()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_TIER_PRICE);
    }

    /**
     * @return mixed
     */
    public function getSkipSpecialPriceConfigurable()
    {
        return $this->getConfigValue(self::SECTION_SKIP_PRICE, self::FIELD_SKIP_SPECIAL_PRICE_CONFIGURABLE);
    }

    /**
     * @return mixed
     */
    public function getShowDiscountBreakdown()
    {
        return $this->getConfigValue(self::SECTION_DISCOUNT_BREAKDOWN, self::FIELD_DISCOUNT_BREAKDOWN_ENABLE);
    }

    /**
     * @return bool
     */
    public function isDebugEnable()
    {
        return (bool) $this->getConfigValue(self::SECTION_DEBUG, self::FIELD_DEBUG_ENABLE);
    }

    /**
     * @return string|null
     */
    public function getAllowedIps()
    {
        return $this->getConfigValue(self::SECTION_DEBUG, self::FIELD_ALLOWED_IP);
    }

    /**
     * @param string $section
     * @param string $field
     * @param string $module
     * @return mixed
     */
    private function getConfigValue($section, $field, $module = self::MODULE)
    {
        return $this->scopeConfig->getValue(
            $module . '/' . $section . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
