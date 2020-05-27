<?php

namespace Acidgreen\SploshBackorder\Model\Stockzone;

use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel
{
	protected function _construct()
	{
		$this->_init('Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item');
	}

    /**
     * Get splosh_inventory_stockzone_item records for a product
     * Returns an array keyed by stockzone ID
     * @param string $sku
     * @return Item[]
     */
    public function getProductStockzoneItems($sku)
    {
        $stockzoneItems = [];

        $collection = $this->getCollection();
        $collection->addFieldToFilter('sku', $sku);

        if (count($collection) > 0) {
            foreach ($collection as $item) {
                $stockzoneItems[$item->getSploshStockzoneId()] = $item;
            }
        }

        return $stockzoneItems;
    }
}
