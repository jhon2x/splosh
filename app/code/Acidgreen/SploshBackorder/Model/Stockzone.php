<?php

namespace Acidgreen\SploshBackorder\Model;

class Stockzone extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone');
	}

    /**
     * Get stockzone by website Id
     * @param \Magento\Store\Api\Data\WebsiteInterface $website
     * @return \Acidgreen\SploshBackorder\Model\Stockzone
     */
    public function getStockzoneByWebsite($website)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('website_id', $website->getId());

        if (count($collection) > 0)
            return $collection->getFirstItem();

        return null;
    }

    /**
     * Get stock item details by website scope
     * @param Stockzone $stockZone
     */
    public function getItemsForWebsite()
    {
        $items = [];
        $collection  = $this->getCollection();
        $collection->addFieldToFilter('splosh_stockzone_id', $this->getId());

        if (count($collection) > 0) {
            foreach ($collection as $item) {
                $items[$item->getSku()] = $item;
            }
        }

        return $items;
    }
}
