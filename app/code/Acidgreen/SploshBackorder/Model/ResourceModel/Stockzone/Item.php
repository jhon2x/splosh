<?php

namespace Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone;

use Magento\Framework\App\ObjectManager;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $tableName = 'splosh_inventory_stockzone_item';
	protected $parentTableName = 'splosh_inventory_stockzone';
	protected function _construct()
	{
		$this->_init($this->tableName, 'id');
	}
	
	public function getItemByWebsite($sku, $websiteId)
	{
		
		$sql = "SELECT * FROM {$this->tableName} 
			WHERE sku = :sku 
			AND splosh_stockzone_id = (
				SELECT id FROM {$this->parentTableName} 
				WHERE website_id = :website_id LIMIT 1) LIMIT 1";
		$bind = [
			'sku' => $sku,
			'website_id' => $websiteId
		];
		
		return $this->_getItemBySql($sql, $bind);
	}
	
	public function getItemByProductIdWebsite($productId, $websiteId)
	{
		$sql = "SELECT * FROM {$this->tableName}
			WHERE product_id = :product_id
			AND splosh_stockzone_id = (
				SELECT id FROM {$this->parentTableName}
				WHERE website_id = :website_id LIMIT 1) LIMIT 1";
		$bind = [
			'product_id' => $productId,
			'website_id' => $websiteId
		];
		
		return $this->_getItemBySql($sql, $bind);
	}
	
	/**
	 * Get stockzone item entries for cart items
	 * @param array $productIds
	 * @param int $websiteId
	 * @return array|boolean
	 */
	public function getCartItems($productIds, $websiteId)
	{

		$productIds = implode(',', $productIds);
		
		$sql = "SELECT * FROM {$this->tableName}
			WHERE splosh_stockzone_id = (
				SELECT id FROM {$this->parentTableName} 
				WHERE website_id = :website_id LIMIT 1)
			AND product_id IN (".$productIds.")";
		
		$bind = [
			'website_id' => $websiteId,
		];
		
		$tempCartItems = $this->_getItemBySql($sql, $bind, false);
		
		$cartItems = [];
		
		if (!empty($tempCartItems)) {
			foreach ($tempCartItems as $item) {
				$cartItems[$item['product_id']] = $item;	
			}
		}
		
		return $cartItems;
	}
	
	/**
	 * get Stockzone items thru SQL
	 * @param string $sql
	 * @param array $bind
	 * @param boolean $fetchOne
	 * @return \Magento\Framework\DataObject|array|boolean
	 */
	private function _getItemBySql($sql, $bind, $fetchOne = true)
	{
		$connection = $this->getConnection();
		$result = $connection->fetchAssoc($sql, $bind);
		
		if ($result) {
			if ($fetchOne) {
				$item = array_shift($result);
				
				return new \Magento\Framework\DataObject($item);
			}
			return $result;
		}
		return false;
	}
}
