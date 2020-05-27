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



namespace Mirasvit\EmailReport\Ui\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\EmailReport\Api\Data\ClickInterface;
use Mirasvit\EmailReport\Api\Data\EmailInterface;
use Mirasvit\EmailReport\Api\Data\OpenInterface;
use Mirasvit\EmailReport\Api\Data\OrderInterface;
use Mirasvit\EmailReport\Api\Data\ReviewInterface;
use Mirasvit\EmailReport\Api\Repository\ClickRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\EmailRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\OpenRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\OrderRepositoryInterface;
use Mirasvit\EmailReport\Api\Repository\ReviewRepositoryInterface;
use Mirasvit\EmailReport\Model\ResourceModel\CollectionTrait;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class DataProviderModifier implements ModifierInterface
{
    private $emailRepository;

    private $openRepository;

    private $clickRepository;

    private $orderRepository;

    private $reviewRepository;

    private $pricingHelper;

    public function __construct(
        EmailRepositoryInterface $emailRepository,
        OpenRepositoryInterface $openRepository,
        ClickRepositoryInterface $clickRepository,
        OrderRepositoryInterface $orderRepository,
        ReviewRepositoryInterface $reviewRepository,
        PricingHelper $pricingHelper
    ) {
        $this->emailRepository = $emailRepository;
        $this->openRepository = $openRepository;
        $this->clickRepository = $clickRepository;
        $this->orderRepository = $orderRepository;
        $this->reviewRepository = $reviewRepository;
        $this->pricingHelper = $pricingHelper;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    public function modifyData(array $data)
    {
        $data['report']['emailCount'] = $this->getCollection($data, $this->emailRepository)
            ->aggregate(EmailInterface::ID);

        $data['report']['openCount'] = $this->getCollection($data, $this->openRepository)
            ->aggregate(OpenInterface::ID);

        $data['report']['clickCount'] = $this->getCollection($data, $this->clickRepository)
            ->aggregate(ClickInterface::ID);

        $data['report']['orderCount'] = $this->getCollection($data, $this->orderRepository)
            ->aggregate(OrderInterface::ID);

        $data['report']['reviewCount'] = $this->getCollection($data, $this->reviewRepository)
            ->aggregate(ReviewInterface::ID);

        $sum = $this->getCollection($data, $this->orderRepository)
            ->aggregate(OrderInterface::AMOUNT, 'SUM');
        $data['report']['amountSum'] = $sum ? $this->pricingHelper->currency(
            $sum,
            true,
            false
        ) : 0;

        $data['report'] = $this->addRates($data['report']);

        foreach ($data['report'] as $key => $value) {
            if (!$value) {
                $data['report'][$key] = '-';
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param object $repository
     * @return CollectionTrait
     */
    private function getCollection($data, $repository)
    {
        $collection = $repository
            ->getCollection()
            ->joinQueue();

        if (isset($data['id_field_name'])) {
            $entityId = $data[$data['id_field_name']];
            $collection->addFieldToFilter($data['id_field_name'], $entityId);
        }

        return $collection;
    }

    /**
     * @param int[] $report
     * @return int[]
     */
    private function addRates($report)
    {
        $rateRelation = [
            'openRate'   => ['emailCount', 'openCount'],
            'clickRate'  => ['emailCount', 'clickCount'],
            'orderRate'  => ['emailCount', 'orderCount'],
            'reviewRate' => ['emailCount', 'reviewCount'],
        ];

        foreach ($rateRelation as $rateKey => $relation) {
            if (isset($report[$relation[0]], $report[$relation[1]]) && $report[$relation[0]] && $report[$relation[1]]) {
                $report[$rateKey] = round(100 / $report[$relation[0]] * $report[$relation[1]]) . '%';
            } else {
                $report[$rateKey] = '-';
            }
        }

        return $report;
    }
}
