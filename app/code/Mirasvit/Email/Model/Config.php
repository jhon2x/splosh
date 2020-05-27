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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Test email
     *
     * @return string
     */
    public function getTestEmail()
    {
        $email = $this->scopeConfig->getValue('email/test/email');
        if (!$email) {
            $email = $this->scopeConfig->getValue('trans_email/ident_general/email');
        }

        return $email;
    }

    /**
     * Is sandbox mode enabled
     *
     * @return bool
     */
    public function isSandbox()
    {
        return (bool)$this->scopeConfig->getValue('email/test/sandbox');
    }

    /**
     * Sandbox email
     *
     * @return string
     */
    public function getSandboxEmail()
    {
        return $this->scopeConfig->getValue('email/test/email');
    }

    /**
     * Email limit
     *
     * @return int
     */
    public function getEmailLimit()
    {
        return (int)$this->scopeConfig->getValue('email/general/max_email');
    }

    /**
     * Limit of email
     *
     * @return int
     */
    public function getEmailLimitPeriod()
    {
        return (int)$this->scopeConfig->getValue('email/general/max_email_period');
    }

    /**
     * @return int
     */
    public function getCouponLength()
    {
        return (int)$this->scopeConfig->getValue('email/coupon/length');
    }

    /**
     * @return string
     */
    public function getCouponPrefix()
    {
        return $this->scopeConfig->getValue('email/coupon/prefix');
    }

    /**
     * @return string
     */
    public function getCouponSuffix()
    {
        return $this->scopeConfig->getValue('email/coupon/suffix');
    }

    /**
     * @return int
     */
    public function getCouponDash()
    {
        return (int)$this->scopeConfig->getValue('email/coupon/dash');
    }

    public function getFacebookUrl()
    {
        return $this->scopeConfig->getValue('email/info/facebook_url');
    }

    public function getTwitterUrl()
    {
        return $this->scopeConfig->getValue('email/info/twitter_url');
    }
}
