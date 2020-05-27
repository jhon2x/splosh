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


namespace Mirasvit\Email\EmailDesigner\Variable\Liquid;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\AbstractVariable;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Model\ProductFactory;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Order;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Quote;

class CrossSell extends AbstractVariable
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var Order
     */
    private $orderVar;
    /**
     * @var Quote
     */
    private $quoteVar;

    public function __construct(
        Order $orderVar,
        Quote $quoteVar,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        LayoutInterface $layout
    ) {
        parent::__construct();

        $this->productCollectionFactory = $productCollectionFactory;
        $this->layout = $layout;
        $this->productFactory = $productFactory;
        $this->orderVar = $orderVar;
        $this->quoteVar = $quoteVar;
    }

    /**
     * Get block with cross sell products (depends on selected source)
     *
     * @return string
     */
    public function getCrossSellHtml()
    {
        $collection = $this->getCollection();

        /** @var \Mirasvit\Email\Block\CrossSell $crossBlock */
        $crossBlock = $this->layout->createBlock('Mirasvit\Email\Block\CrossSell');

        $crossBlock->setCollection($collection);

        return $crossBlock->toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getCollection()
    {
        $productIds = $this->getProductIds();
        $productIds[] = 0;

        $collection = $this->productCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('name')
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        $collection->getSelect()->reset('order');

        return $collection;
    }

    /**
     * @return array
     */
    protected function getProductIds()
    {
        if ($this->context->getData('preview')) {
            $collection = $this->productCollectionFactory->create();
            $collection->getSelect()
                ->orderRand()
                ->limit(20);

            return $collection->getAllIds(20);
        } else {
            $result = [];
            /** @var ChainInterface $chain */
            if ($this->context->getData('queue') && ($chain = $this->context->getData('queue')->getChain())) {
                if ($chain->getCrossSellsEnabled()) {
                    /** @var \Magento\Sales\Model\Order $order */
                    $baseProducts = $this->getBaseProducts();
                    foreach ($baseProducts as $baseProduct) {
                        if ($baseProduct instanceof \Magento\Catalog\Model\Product
                            && ($methodName = $chain->getCrossSellMethodName()) !== null
                        ) {
                            foreach ($baseProduct->$methodName() as $id) {
                                $result[] = $id;
                            }
                        }
                    }
                }
            }

            return $result;
        }
    }

    /**
     * @return array
     */
    protected function getBaseProducts()
    {
        $result = [];

        $this->orderVar->setContext($this->context);
        $this->quoteVar->setContext($this->context);

        if ($this->orderVar->getOrder()) {
            foreach ($this->orderVar->getOrder()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->quoteVar->getQuote() && count($result) == 0) {
            foreach ($this->quoteVar->getQuote()->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->context->getData('product_id') && count($result) == 0) {
            $result[] = $this->productFactory->create()->load($this->context->getData('product_id'));
        }

        return array_filter($result);
    }
}
