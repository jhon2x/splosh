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
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\ProductData;

class QtyEvent extends ObservableEvent
{
    const IDENTIFIER = 'qty_reduced';

    const PARAM_QTY_NEW = 'qty';
    const PARAM_QTY_OLD = 'old_qty';

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        Context $context
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Product / Decreased QTY'),
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
        $product = $this->context->create(Product::class)->load($params[ProductData::ID]);

        $params[ProductData::IDENTIFIER] = $product;

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

        return __('QTY decreased for product %1 from %2 to %3 items',
            $product->getSku(),
            $params[self::PARAM_QTY_OLD],
            $params[self::PARAM_QTY_NEW]
        );
    }

    /**
     * Register an event if product QTY has been reduced while modifying a product.
     *
     * @param Product $subject
     * @param mixed   $result
     *
     * @return mixed $result
     */
    public function afterSave(Product $subject, $result)
    {
        //$stockItem = $this->stockRegistryProvider->getStockItem($subject->getId(), $subject->getStore()->getWebsiteId());
        //$stockStatus = $this->stockRegistryProvider->getStockStatus($subject->getId(), $subject->getStore()->getWebsiteId());
        //$newQty = $stockItem->getQty(); // method returns already changed QTY

        $qty = $subject->getData('quantity_and_stock_status/qty') !== null
            ? $subject->getData('quantity_and_stock_status/qty')
            : $subject->getData('stock_data/qty');
        $oldQtyArr = $subject->getOrigData('quantity_and_stock_status');
        $oldQty = is_array($oldQtyArr) && isset($oldQtyArr['qty']) ? $oldQtyArr['qty'] : false;

        if ($qty !== null && $oldQty !== false && $oldQty > $qty) {
            $params = [
                ProductData::ID          => $subject->getId(),
                self::PARAM_QTY_NEW      => $qty,
                self::PARAM_QTY_OLD      => $oldQty,
                self::PARAM_EXPIRE_AFTER => 1
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[ProductData::ID]],
                $params
            );
        }

        return $result;
    }

    /**
     * Register an event if product QTY has been reduced while placing an order.
     *
     * @param Stock    $stock
     * @param callable $proceed
     * @param int[]    $items
     * @param int      $websiteId
     * @param string   $operator +/-
     *
     * @return void
     */
    public function aroundCorrectItemsQty(Stock $stock, callable $proceed, array $items, $websiteId, $operator)
    {
        if ($operator === '-') {
            foreach ($items as $productId => $qty) {
                $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
                $newQty = $stockItem->getQty(); // method returns already changed QTY
                $oldQty = $stockItem->getQty() + $qty; // so to get old QTY we should add the subtracted QTY

                if ($oldQty > $newQty) {
                    $params = [
                        ProductData::ID          => $productId,
                        self::PARAM_QTY_NEW      => $newQty,
                        self::PARAM_QTY_OLD      => $oldQty,
                        self::PARAM_EXPIRE_AFTER => 1
                    ];

                    $this->context->eventRepository->register(
                        self::IDENTIFIER,
                        [$params[ProductData::ID]],
                        $params
                    );
                }
            }
        }

        $proceed($items, $websiteId, $operator);
    }
}
