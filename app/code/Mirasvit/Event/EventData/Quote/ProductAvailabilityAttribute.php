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



namespace Mirasvit\Event\EventData\Quote;


use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\AttributeInterface;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\EventData\Condition\QuoteCondition;
use Mirasvit\Event\EventData\QuoteData;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class ProductAvailabilityAttribute implements AttributeInterface
{
    const ATTR_CODE  = 'product_availability';
    const ATTR_LABEL = 'Shopping Cart products available for purchase';
    /**
     * @var
     */
    private $stockRegistry;

    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return self::ATTR_CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return __(self::ATTR_LABEL);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return EventDataInterface::ATTRIBUTE_TYPE_BOOL;
    }

    /**
     * Check whether the products in quote are available for purchase still.
     *
     * {@inheritDoc}
     */
    public function getValue(AbstractModel $dataObject)
    {
        $value = true;
        /** @var QuoteData $quote */
        $quote = $dataObject->getData(QuoteData::IDENTIFIER);

        /** @var QuoteItem $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (!$product) {
                $value = false;
                break;
            }

            $stockItem = $this->stockRegistry->getStockItem($item->getProductId());
            if (
                !$stockItem->getIsInStock()
                || ($product->getQty() > 0 && $product->getQty() < $item->getQty())
            ) {
                $value = false;
                break;
            }
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionClass()
    {
        return QuoteCondition::class . '|' . self::ATTR_CODE;
    }
}
