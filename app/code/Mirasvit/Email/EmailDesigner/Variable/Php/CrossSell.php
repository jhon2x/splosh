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


namespace Mirasvit\Email\EmailDesigner\Variable\Php;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirasvit\Email\Api\Data\ChainInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Php\Variable\Context;
use Magento\Framework\View\Element\BlockFactory;

class CrossSell
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var BlockFactory
     */
    private $blockFactory;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param ProductFactory           $productFactory
     * @param Context                  $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param BlockFactory             $blockFactory
     */
    public function __construct(
        ProductFactory $productFactory,
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        BlockFactory $blockFactory
    ) {
        $this->context = $context;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->blockFactory = $blockFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @return string
     */
    public function getCrossSellHtml()
    {
        $collection = $this->getCollection();

        /** @var \Mirasvit\Email\Block\CrossSell $crossBlock */
        $crossBlock = $this->blockFactory->createBlock(\Mirasvit\Email\Block\CrossSell::class);

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
     * @return array|null
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
            if ($this->context->getData('queue') && ($chain = $this->context->getData('queue')->getChain())) {
                /** @var ChainInterface $chain */
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

        if ($this->context->getOrder()) {
            foreach ($this->context->getData('order')->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->context->getQuote() && count($result) == 0) {
            foreach ($this->context->getData('quote')->getAllVisibleItems() as $item) {
                $result[] = $item->getProduct();
            }
        }

        if ($this->context->getData('product_id') && count($result) == 0) {
            $result[] = $this->productFactory->create()->load($this->context->getData('product_id'));
        }

        return array_filter($result);
    }
}
