<?php
namespace Acidgreen\OrderComments\Plugin;

class PaymentInformationManagementPlugin
{

	protected $historyFactory;

	protected $orderFactory;

    /**
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
    		\Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
    		\Magento\Sales\Model\OrderFactory $orderFactory
    ) {
    		$this->historyFactory = $historyFactory;
    		$this->orderFactory = $orderFactory;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
	   * @param int $cartId
	   * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
	   * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
	   *
	   * @return int $orderId
    */
    public function aroundSavePaymentInformationAndPlaceOrder(
    		\Magento\Checkout\Model\PaymentInformationManagement $subject,
    		\Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
    		$comment = NULL;
    		$request_body = file_get_contents('php://input');
    		$data = json_decode($request_body, true);

    		if (isset ($data['comments'])) {
      			if ($data['comments']) {
      				$comment = strip_tags ($data['comments']);
      			}
    		}

    		$orderId = $proceed($cartId, $paymentMethod, $billingAddress);

    		if ($comment) {
      			$order = $this->orderFactory->create()->load($orderId);

      			if ($order->getData('entity_id')) {
        				$status = $order->getData('status');

        				$history = $this->historyFactory->create();

        				$history->setData('comment', $comment);
        				$history->setData('parent_id', $orderId);
        				$history->setData('is_visible_on_front', 1);
        				$history->setData('is_customer_notified', 0);
        				$history->setData('entity_name', 'order');
        				$history->setData('status', $status);
        				$history->save();
      			}
    		}
    		return $orderId;
    }
}
