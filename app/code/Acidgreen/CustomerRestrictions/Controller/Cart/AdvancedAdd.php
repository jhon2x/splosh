<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Acidgreen\CustomerRestrictions\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class AdvancedAdd extends \Magento\AdvancedCheckout\Controller\Cart\AdvancedAdd
{
    protected $agCheckoutHelper;

    protected $customerRestrictions;

    protected $productRepositoryInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Acidgreen\Checkout\Helper\Cart $agCheckoutHelper,
        \Acidgreen\CustomerRestrictions\Helper\Restrictions $customerRestrictions,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
    ) {
        $this->agCheckoutHelper = $agCheckoutHelper;
        $this->customerRestrictions = $customerRestrictions;
        $this->productRepositoryInterface = $productRepositoryInterface;

        parent::__construct($context);
    }

    /**
     * Overwrite class to prevent customer on adding restricted product using SKU
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $hasRestriction = false;
        $restrictedCounter = 0;
        $restrictedSku = array();
        $counter = 0;

        if($this->agCheckoutHelper->isSiteB2b()){
            $rangeRestrictions = $this->customerRestrictions->getRestriction("range");

            if($rangeRestrictions && !empty($rangeRestrictions)){
                $hasRestriction = true;
            }
        }

        // check empty data
        /** @var $helper \Magento\AdvancedCheckout\Helper\Data */
        $helper = $this->_objectManager->get('Magento\AdvancedCheckout\Helper\Data');
        $items = $this->getRequest()->getParam('items');

        foreach ($items as $k => $item) {
            if (!isset($item['sku']) || (empty($item['sku']) && $item['sku'] !== '0')) {
                unset($items[$k]);
            }

            if( $hasRestriction && isset($item['sku']) ){
                $isRestricted = $this->checkIfProductIsRestricted($item['sku'], $rangeRestrictions);

                if($isRestricted){
                    $productName = $this->getProductNameBySku($item['sku']);

                    $restrictedSku[$restrictedCounter]["sku"] = $item['sku'];
                    $restrictedSku[$restrictedCounter]["name"] = $productName;

                    unset($items[$k]);

                    $restrictedCounter++;
                }
            }
        }

        if($restrictedCounter > 0){
            $restrictedProducts = "";

            foreach ($restrictedSku as $item) {
                $restrictedProducts .= $item["sku"] . " - " . $item["name"] . "</br>";
            }

            $this->messageManager->addError("We are sorry, some products were not added to your cart due to restrictions to your account. For any enquiries or questions please send us a message through our <a href=\"/contact-us\">Contact Form</a> or phone our friendly customer service team.</br>
                Products not added are:</br>" . $restrictedProducts);
        }

        //if all items are restricted, add message and redirect to cart already
        if($restrictedCounter == count($items)){
            return $resultRedirect->setPath('checkout/cart');
        }

        if (empty($items) && !$helper->isSkuFileUploaded($this->getRequest())) {
            $this->messageManager->addError($helper->getSkuEmptyDataMessageText());
            return $resultRedirect->setPath('checkout/cart');
        }

        try {
            // perform data
            $cart = $this->_getFailedItemsCart()->prepareAddProductsBySku($items)->saveAffectedProducts();

            $this->messageManager->addMessages($cart->getMessages());

            if ($cart->hasErrorMessage()) {
                throw new LocalizedException(__($cart->getErrorMessage()));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
        $this->_eventManager->dispatch('collect_totals_failed_items');

        return $resultRedirect->setPath('checkout/cart');
    }

    /**
     * Check if product is restricted
     *
     * @param string $sku
     * @param array $rangeRestrictions
     * @return bool
     */
    private function checkIfProductIsRestricted($sku, $rangeRestrictions){
        try{
            $product = $this->productRepositoryInterface->get($sku);

            if($product->getId()){
                $productRange = $product->getRange();

                if($productRange){
                    if(in_array($productRange, $rangeRestrictions))
                        return true;
                }
            }
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            return false;
        }

        return false;
    }

    /**
     * get product name by sku
     *
     * @param string $sku
     * @return bool|string
     */
    private function getProductNameBySku($sku){
        try{
            $product = $this->productRepositoryInterface->get($sku);

            return $product->getName();
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e){
            return false;
        }

        return false;
    }
}
