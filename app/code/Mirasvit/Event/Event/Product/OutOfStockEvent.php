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



namespace Mirasvit\Event\Event\Product;

use Magento\Catalog\Model\Product;
use Magento\Inventory\Model\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\ProductData;

class OutOfStockEvent extends ObservableEvent
{
    const IDENTIFIER = 'outofstock';

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        ProductMetadataInterface $metadata,
        Context $context
    ) {
        $this->metadata = $metadata;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Product / Out Of Stock'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(ProductData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $params[ProductData::IDENTIFIER] = $this->context->create(Product::class)->load($params[ProductData::ID]);

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var Product $product */
        $product = $params[ProductData::IDENTIFIER];

        return __('Product %1 is out of stock now', $product->getSku());
    }

    /**
     * Register an event if when product becomes out of stock.
     *
     * @param SourceItem $subject
     */
    public function afterSetStatus(SourceItem $subject)
    {
        if ($this->isOutOfStock($subject)) {
            $params = [
                ProductData::ID          => $this->context->get(Product::class)->getIdBySku($subject->getSku()),
                self::PARAM_EXPIRE_AFTER => 3600
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[ProductData::ID]],
                $params
            );
        }
    }

    /**
     * @param SourceItemInterface|SourceItem $sourceItem
     *
     * @return bool
     */
    private function isOutOfStock(SourceItemInterface $sourceItem)
    {
        $isOutOfStock = false;
        if (($sourceItem->dataHasChangedFor(SourceItemInterface::STATUS)
                && $sourceItem->getStatus() === SourceItemInterface::STATUS_OUT_OF_STOCK)
            || ($sourceItem->dataHasChangedFor(SourceItemInterface::QUANTITY)
                && $sourceItem->getQuantity() === 0)
        ) {
            /** @var GetSourceItemsBySkuInterface $getSourceItemsBySku */
            $getSourceItemsBySku = $this->context->get(GetSourceItemsBySkuInterface::class);
            $items               = $getSourceItemsBySku->execute($sourceItem->getSku());
            $isOutOfStock        = true;

            foreach ($items as $item) {
                if ($item->getSourceCode() !== $sourceItem->getSourceCode()) {
                    if ($item->getStatus() === SourceItemInterface::STATUS_IN_STOCK && $item->getQuantity() > 0) {
                        $isOutOfStock = false;
                        break;
                    }
                }
            }
        }

        return $isOutOfStock;
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        return version_compare($this->metadata->getVersion(), '2.3.0', '>=');
    }
}
