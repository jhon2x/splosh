<?php

namespace Acidgreen\SploshBackorder\Plugin\Model;

class Product
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->appState = $appState;
        $this->productRepository = $productRepository;
    }
    /**
     * Save exo_due_date / force_backorder on a default scope, if empty..
     * @param \Magento\Catalog\Model\Product $product
     * @return $result
     */
    public function afterSave(
        \Magento\Catalog\Model\Product $product,
        $result
    ) {
        if ('adminhtml' === $this->appState->getAreaCode()) {
            // set exo_due_date (or force_backorder) IF empty
            // this is for getCollection / load() purposes
            // we load a product by SKU, edit_mode = true, storeId 0
            $adminProduct = $this->productRepository->get($result->getSku(), true, 0);


            /**
             * SPL-372 - default force_backorder and exo_due_date on admin scope (in anticipation for packs)
             */
            if (empty($adminProduct->getExoDueDate())) {
                $adminProduct->setData('exo_due_date', '-');
                $adminProduct->getResource()->saveAttribute($adminProduct, 'exo_due_date');
            }

            if ($adminProduct->getForceBackorder() === null) {
                $adminProduct->setData('force_backorder', '0');
                $adminProduct->getResource()->saveAttribute($adminProduct, 'force_backorder');
            }

        }

        return $result;
    }
}
