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


namespace Mirasvit\Event\EventData\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as CatalogRuleProductFactory;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Event\EventData\OrderData;
use Mirasvit\Event\EventData\QuoteData;

class Subselect extends Combine
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        CatalogRuleProductFactory $catalogRuleProductFactory,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        parent::__construct($context, $catalogRuleProductFactory, $data);

        $this->setData('type', self::class)
            ->setData('value', null);

        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml()
            . __(
                'If %1 products in cart/order matching these conditions:',
                $this->getAggregatorElement()->getHtml()
            );

        if ($this->getData('id') != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(AbstractModel $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $this->setData('value', 1);
        $aggregator = $this->getData('aggregator');
        if ($aggregator != 'any') {
            $this->setData('aggregator', 'all');
        }

        if ($collection = $this->getCollection($object)) {
            $total = 0;
            $count = count($collection);
            foreach ($collection as $item) {
                if (parent::validate($item) || parent::validate($item->getProduct())) {
                    ++$total;
                }
            }

            if ($aggregator == 'any') {
                return $total > 0;
            } elseif ($aggregator == 'all') {
                return $total == $count;
            } elseif (is_numeric($aggregator)) {
                return $total == $aggregator;
            }
        }

        return true;
    }

    /**
     * @param AbstractModel $object
     *
     * @return \Magento\Sales\Model\Order\Item[]|\Magento\Quote\Model\Quote\Item[]|null
     */
    private function getCollection(AbstractModel $object)
    {
        $collection = null;
        if ($object->getData(QuoteData::ID)) {
            /** @var Quote $quote */
            $quote = $this->quoteFactory->create()
                ->setSharedStoreIds(array_keys($this->storeManager->getStores()))
                ->load($object->getData(QuoteData::ID));

            $collection = $quote->getItemsCollection();
        } elseif ($object->getData(OrderData::ID)) {
            /** @var Order $order */
            $order = $this->orderFactory->create()->load($object->getData(OrderData::ID));

            $collection = $order->getItemsCollection();
        }

        return $collection;
    }

    /**
     * Add additional aggregator options to condition.
     * Allow validate concrete number of products (from 1 to 10).
     *
     * {@inheritdoc}
     */
    public function loadAggregatorOptions()
    {
        parent::loadAggregatorOptions();
        $options = $this->getData('aggregator_option');
        for ($i = 1; $i <= 10; $i++) {
            $options[$i] = $i;
        }

        $this->setData('aggregator_option', $options);

        return $this;
    }
}
