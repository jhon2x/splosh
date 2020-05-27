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

use Magento\Review\Model\Review;
use Mirasvit\EmailReport\Api\Repository\ReviewRepositoryInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;

class ReviewPlugin
{
    /**
     * @var StorageServiceInterface
     */
    private $storageService;

    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    public function __construct(
        StorageServiceInterface $storageService,
        ReviewRepositoryInterface $reviewRepository
    ) {
        $this->storageService = $storageService;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @param Review $review
     * @return Review
     */
    public function afterSave($review)
    {
        if ($queue = $this->storageService->retrieveQueue()) {
            $review = $this->reviewRepository->create()
                ->setTriggerId($queue->getTriggerId())
                ->setQueueId($queue->getId())
                ->setParentId($review->getId());

            $this->reviewRepository->ensure($review);
        }

        return $review;
    }
}
