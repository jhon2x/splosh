<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\ScalableInventory\Model\ResourceModel;

use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\ScalableInventory\Model\Counter\ItemsBuilder;
use Magento\ScalableInventory\Model\ResourceModel\QtyCounter as CoreQtyCounter;
use Psr\Log\LoggerInterface;

/**
 * Class QtyCounter
 */
class QtyCounter extends CoreQtyCounter
{
    const TOPIC_NAME = 'inventory.counter.updated';

    /**
     * @var ItemsBuilder
     */
    private $itemsBuilder;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * QtyCounter constructor.
     *
     * @param ItemsBuilder $itemsBuilder
     * @param PublisherInterface $publisher
     */
    public function __construct(
        ItemsBuilder $itemsBuilder, 
        PublisherInterface $publisher, 
        LoggerInterface $logger
    ) {
        $this->itemsBuilder = $itemsBuilder;
        $this->publisher = $publisher;
        $this->logger = $logger;
        parent::__construct($itemsBuilder, $publisher);
    }

    /**
     * {@inheritdoc}
     */
    public function correctItemsQty(array $items, $websiteId, $operator)
    {
        $this->logger->debug(__METHOD__.':: items, websiteId, and operator dump');
        $this->logger->debug(print_r($items, true));
        $this->logger->debug(print_r($websiteId, true));
        $this->logger->debug(print_r($operator, true));

        $items = $this->itemsBuilder->build($items, $websiteId, $operator);
        // $this->publisher->publish(self::TOPIC_NAME, $items);
    }
}
