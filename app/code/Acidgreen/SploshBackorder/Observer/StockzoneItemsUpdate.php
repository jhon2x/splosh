<?php

namespace Acidgreen\SploshBackorder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Acidgreen\SploshBackorder\Model\StockzoneRegistry;
use Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory as StockzoneItemFactory;

class StockzoneItemsUpdate implements ObserverInterface
{
    /**
     * @var StockzoneRegistry
     */
    private $stockzoneRegistry;

    /**
     * @var StockzoneItemFactory
     */
    private $stockzoneItemFactory;

    public function __construct(
        StockzoneRegistry $stockzoneRegistry,
        StockzoneItemFactory $stockzoneItemFactory
    ) {
        $this->stockzoneRegistry = $stockzoneRegistry;
        $this->stockzoneItemFactory = $stockzoneItemFactory;
    }

    public function execute(Observer $observer) 
    {
        $product = $observer->getEvent()->getProduct();
        $productPostData = $observer->getEvent()->getController()->getRequest()->getParam('product');
        $stockzoneItemsData = $productPostData['stockzone_data'];

        // process 'em here...
        //
        // load stockzones
        $stockzones = $this->stockzoneRegistry->getStockzones();
        // load existing stockzone items
        $stockzoneItems = $this->stockzoneItemFactory->create()->getProductStockzoneItems($product->getSku());
        // process postdata

        foreach ($stockzoneItemsData as $stockzoneId => $data) {
            // update existing, create one if it doesn't exist
            if (isset($stockzoneItems[$stockzoneId])) {
                // retrieve existing
                $item = $stockzoneItems[$stockzoneId];
                $item->setData('id', $item->getId());
            } else {
                // instantiate model
                $item = $this->stockzoneItemFactory->create();
            }

            try {
                foreach ($data as $key => $value) {
                    $item->setData($key, $value);
                }
                $item->setData('sku', $product->getSku());
                $item->setData('product_id', $product->getId());
                $item->setData('splosh_stockzone_id', $stockzoneId);
                $item->save();

            } catch (\Exception $e) {
                $message = $e->getMessage();
                // throw \Magento\Framework\Exception\CouldNotSaveException('Could not save item to stock zone. '.$message);
            }

        }

        return;
    }
}
