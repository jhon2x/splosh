<?php

namespace Acidgreen\GoogleTagManager\Helper;

/**
 * Extend Magento_GoogleTagManager helper
 * To consider the exempted actions when setting the add_to_cart cookie
 */
class Data extends \Magento\GoogleTagManager\Helper\Data
{
    const XML_EXEMPTIONS_PATH = 'google/exemptions/full_action_name_exemptions';
    /**
     * Whether GTM is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function isTagManagerAvailable($store = null)
    {
        $exemptions = $this->scopeConfig->getValue(self::XML_EXEMPTIONS_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

        $fullActionName = $this->_request->getFullActionName();

        if (!empty($exemptions)) {
            if (preg_match("/".$fullActionName."/", $exemptions)) {
                $this->_logger->debug('SPL-459 :: Avoid add_to_cart cookie setting this time. ACTION :: '.$fullActionName);
                return false;
            }
        }
        return parent::isTagManagerAvailable($store);
    }
}
