<?php
namespace Acidgreen\OrderComments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class CustomBlockConfigProvider implements ConfigProviderInterface
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfiguration;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
    }

    /**
     * @return array() $showHide
     */
    public function getConfig()
    {
        $showHide = [];

        //$enabled = $this->scopeConfiguration->getValue('checkout/options/onepage_checkout_comments_enabled', ScopeInterface::SCOPE_STORE);
        $showHide['show_hide_custom_block'] = true;//($enabled)?true:false;
        return $showHide;
    }
}
