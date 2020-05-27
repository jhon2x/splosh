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
 * @package   mirasvit/module-email-designer
 * @version   1.1.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailDesigner\Model\Email;


use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;

class Context
{
    /**
     * Array of variable codes that can be used in the transactional emails.
     *
     * @var array
     */
    private $defaultVariables = ['order', 'customer'];
    /**
     * @var Renderer
     */
    private $addressRenderer;
    /**
     * @var PaymentHelper
     */
    private $paymentHelper;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    public function __construct(
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        Emulation $appEmulation
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->appEmulation = $appEmulation;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Add variables that are used by emails.
     *
     * @param array $variables
     * @param null|string|bool|int|Store $storeId
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addEmailVariables($variables, $storeId)
    {
        foreach ($this->defaultVariables as $code) {
            $id = isset($variables[$code.'_id']) ? $variables[$code.'_id'] : null;
            if (isset($variables[$code]) || !$id) {
                continue;
            }

            switch ($code) {
                case 'order':
                    $order = $this->orderFactory->create()->load($id);

                    $variables['order'] = $order;
                    $variables['billing'] = $order->getBillingAddress();
                    $variables['formattedShippingAddress'] = $this->getFormattedShippingAddress($order);
                    $variables['formattedBillingAddress'] = $this->getFormattedBillingAddress($order);

                    // if payment method does not exist (outdated|removed) Magento throws an exception,
                    // so we use it only if a method is still available
                    try {
                        if ($order->getPayment()
                            && $order->getPayment()->getMethod()
                            && $this->paymentHelper->getMethodInstance($order->getPayment()->getMethod())
                        ) {
                            $variables['payment_html'] = $this->getPaymentHtml($order, $storeId);

                            $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
                            // inline translation restored after emulation, so we disable it again
                            $this->inlineTranslation->disable();
                        }
                    } catch (\Exception $e) {
                        $this->logger->info($e->getMessage());
                    }
                    break;
                case 'customer':
                    $variables[$code] = $this->customerFactory->create()->load($id);
                    break;
            }
        }

        return $variables;
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @param int   $storeId
     *
     * @return string
     * @throws \Exception
     */
    protected function getPaymentHtml(Order $order, $storeId)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
}