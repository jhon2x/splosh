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
 * @package   mirasvit/module-email-report
 * @version   2.0.8
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Plugin;

use Magento\Sales\Model\Order;
use Mirasvit\EmailReport\Api\Repository\OrderRepositoryInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;

class OrderPlugin
{
    /**
     * @var StorageServiceInterface
     */
    private $storageService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        StorageServiceInterface $storageService,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->storageService = $storageService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param object $subject
     * @param Order $order
     * @return Order
     */
    public function afterSave($subject, $order = null)
    {
        if (!$order) {
            return $order;
        }

        if ($queue = $this->storageService->retrieveQueue()) {
            $model = $this->orderRepository->create()
                ->setTriggerId($queue->getTriggerId())
                ->setQueueId($queue->getId())
                ->setParentId($order->getId())
                ->setAmount($order->getGrandTotal());

            $this->orderRepository->ensure($model);
        }

        return $order;
    }
}
