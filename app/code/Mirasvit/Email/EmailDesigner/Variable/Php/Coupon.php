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


namespace Mirasvit\Email\EmailDesigner\Variable\Php;

use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Coupon\Massgenerator;
use Magento\SalesRule\Model\RuleFactory;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Context;
use Mirasvit\Email\Model\Config;

class Coupon
{
    /**
     * @var array
     */
    protected $coupons = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param CouponFactory $couponFactory
     * @param Massgenerator $couponMassgenerator
     * @param RuleFactory   $ruleFactory
     * @param Context       $context
     * @param Config        $config
     */
    public function __construct(
        CouponFactory $couponFactory,
        Massgenerator $couponMassgenerator,
        RuleFactory $ruleFactory,
        Context $context,
        Config $config
    ) {
        $this->couponFactory = $couponFactory;
        $this->couponMassgenerator = $couponMassgenerator;
        $this->ruleFactory = $ruleFactory;
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * Generate coupon code
     *
     * @return \Magento\SalesRule\Model\Coupon
     */
    public function getCoupon()
    {
        if ($this->context->getData('preview')) {
            // in preview mode, we create fake coupon
            $expirationDate = time() + rand(1, 30) * 24 * 60 * 60;

            $coupon = $this->couponFactory->create();
            $coupon->setCode('EML#####')
                ->setExpirationDate(date(\DateTime::ISO8601, $expirationDate))
                ->setType(1);

            return $coupon;
        } elseif ($this->context->getData('chain')) {
            /** @var \Mirasvit\Email\Model\Trigger\Chain $chain */
            $chain = $this->context->getData('chain');

            # if we already generated coupon for this chain
            if (isset($this->coupons[$this->getCouponKey($chain, $this->context->getData('queue'))])) {
                return $this->coupons[$this->getCouponKey($chain, $this->context->getData('queue'))];
            }

            if ($chain->getCouponEnabled()) {
                $rule = $this->ruleFactory->create()->load($chain->getCouponSalesRuleId());

                if ($rule->getId()) {
                    if ($rule->getUseAutoGeneration()) {
                        $coupon = $this->generateCoupon($rule, $chain);
                    } else {
                        $coupon = $this->couponFactory->create();
                        $coupon->setRule($rule)
                            ->setCode($rule->getCouponCode());
                    }

                    $this->coupons[$this->getCouponKey($chain, $this->context->getData('queue'))] = $coupon;

                    return $coupon;
                }
            }
        }

        return false;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule       $rule
     * @param \Mirasvit\Email\Model\Trigger\Chain $chain
     * @return \Magento\SalesRule\Model\Coupon
     */
    protected function generateCoupon($rule, $chain)
    {
        $generator = $this->couponMassgenerator;
        $generator->addData([
            'length' => $this->config->getCouponLength(),
            'prefix' => $this->config->getCouponPrefix(),
            'suffix' => $this->config->getCouponSuffix(),
            'dash'   => $this->config->getCouponDash(),
        ]);
        $code = $generator->generateCode();

        $coupon = $this->couponFactory->create();
        if ($chain->getCouponExpiresDays()) {
            $expirationDate = time() + $chain->getCouponExpiresDays() * 24 * 60 * 60;
            $coupon->setExpirationDate(date(\DateTime::ISO8601, $expirationDate));
        }

        $coupon->setRule($rule)
            ->setCode($code)
            ->setIsPrimary(false)
            ->setUsageLimit(1)
            ->setUsagePerCustomer(1)
            ->setType(1)
            ->setCreatedAt(date('Y-m-d H:i:s'))
            ->save();

        return $coupon;
    }

    /**
     * Coupon code key consists of chain and queue IDs.
     *
     * @param ChainInterface $chain
     * @param QueueInterface        $queue
     *
     * @return string
     *
     */
    private function getCouponKey(ChainInterface $chain, QueueInterface $queue)
    {
        return $chain->getId().'_'.$queue->getId();
    }
}
