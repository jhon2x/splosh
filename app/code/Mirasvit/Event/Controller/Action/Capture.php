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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Controller\Action;

use Magento\Backend\App\Action\Context;
use Magento\Checkout\Model\CartFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Model\Quote;
use Mirasvit\Event\Api\Service\GeoIpValidatorInterface;
use Mirasvit\Event\Model\Config as EventConfig;
use Mirasvit\Event\Model\Config\Source\CaptureStatus;

class Capture extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CartFactory
     */
    protected $cartFactory;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var GeoIpValidatorInterface
     */
    private $geoIpValidator;
    /**
     * @var EventConfig
     */
    private $eventConfig;

    public function __construct(
        EventConfig $eventConfig,
        GeoIpValidatorInterface $geoIpValidator,
        CustomerSession $customerSession,
        CartFactory $cartRepository,
        Context $context
    ) {
        $this->eventConfig = $eventConfig;
        $this->geoIpValidator = $geoIpValidator;
        $this->customerSession = $customerSession;
        $this->cartFactory = $cartRepository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->canCapture()) {
            $type = $this->getRequest()->getParam('type');
            $value = $this->getRequest()->getParam('value');

            if ($type === 'email') {
                // save customer email to session
                $this->customerSession->setEmail($value);
            }

            /** @var Quote $quote */
            $quote = $this->cartFactory->create()->getQuote();
            if ($quote->getBillingAddress() && $quote->getBillingAddress()->getId()) {
                $billing = $quote->getBillingAddress();
                $billing->setData($type, $value);
                $billing->save();
            }
        }
    }

    /**
     * Determine whether current block can be rendered or not.
     *
     * @return bool
     */
    private function canCapture()
    {
        if ($this->customerSession->isLoggedIn()) {
            return false;
        }

        $status = $this->eventConfig->getCaptureStatus();

        try {
            $result = ($status === CaptureStatus::STATUS_ON
                || ($status === CaptureStatus::STATUS_OFF_EU
                    && $this->geoIpValidator->assertContinentNotEquals('EU'))
            );
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
