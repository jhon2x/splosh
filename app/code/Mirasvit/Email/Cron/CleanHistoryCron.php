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



namespace Mirasvit\Email\Cron;

use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Repository\QueueRepositoryInterface;
use Mirasvit\Event\Api\Repository\EventRepositoryInterface;

class CleanHistoryCron
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var CouponCollectionFactory
     */
    protected $couponCollectionFactory;
    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        QueueRepositoryInterface $queueRepository,
        CouponCollectionFactory $couponCollectionFactory
    ) {
        $this->eventRepository = $eventRepository;
        $this->queueRepository = $queueRepository;
        $this->couponCollectionFactory = $couponCollectionFactory;
    }

    /**
     * Delete old events, emails, coupons
     *
     * @return void
     */
    public function execute()
    {
        $monthAgo = date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60);

        # Step 1. Remove old events
        $collection = $this->eventRepository->getCollection()
            ->addFieldToFilter('updated_at', ['lt' => $monthAgo]);

        foreach ($collection as $event) {
            $this->eventRepository->delete($event);
        }

        # Step 2. Remove old mails
        $collection = $this->queueRepository->getCollection()
            ->addFieldToFilter('status', ['neq' => QueueInterface::STATUS_PENDING])
            ->addFieldToFilter('scheduled_at', ['lt' => $monthAgo]);

        foreach ($collection as $queue) {
            $this->queueRepository->delete($queue);
        }

        # Step 3. Remove old coupons
        $coupons = $this->couponCollectionFactory->create()
            ->addFieldToFilter('code', ['like' => 'EML%'])
            ->addFieldToFilter('expiration_date', [
                'lteq' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            ]);

        foreach ($coupons as $coupon) {
            $coupon->delete();
        }
    }
}
