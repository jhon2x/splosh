<?php

namespace Acidgreen\SploshBackorder\Block\Product\View;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Acidgreen\SploshBackorder\Helper\Product as BackorderProductHelper;

class DueDate extends \Magento\Catalog\Block\Product\View\Attributes
{
	
	/**
	 * @var StockRegistryInterface
	 */
	protected $stockRegistry;

    /**
     * @var BackorderProductHelper
     */
    protected $backorderProductHelper;
	
	/**
	 * @var StockItemInterface
	 */
	protected $stockItem;
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
    	StockRegistryInterface $stockRegistry,
        BackorderProductHelper $backorderProductHelper,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->backorderProductHelper = $backorderProductHelper;
        
        parent::__construct(
            $context,
            $registry,
            $priceCurrency,
            $data
        );
        
    }

    public function getDueDate() {
        return $this->getProduct()->getExoDueDate();
    }
    
    /**
     * @return StockItemInterface
     */
    public function getStockItem()
    {
    	if (empty($this->stockItem))
    		$this->stockItem = $this->stockRegistry->getStockItem($this->getProduct()->getId(), 1); // assumed '1'
    	return $this->stockItem;
    }
    
    /**
     * Check if backorder is enabled
     * @return boolean
     */
    public function isBackorderEnabled()
    {
    	$currentWebsite = $this->_storeManager->getWebsite()->getCode();
    	$configValue = $this->_scopeConfig->getValue(
    		Configuration::XML_PATH_BACKORDERS, 
    		\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, 
    		$currentWebsite);
    	$stockItem = $this->getStockItem();
    	
    	// should return true if:
    	// backorder sys config enabled && use_config_backorders for stockitem disabled
    	// backorder sys config disabled && backorders enabled (not set to BACKORDERS_NO or 0) && use_config_backorders for stockitem disabled
    	
    	if (!empty($configValue) && $configValue) {
    		return ($stockItem->getUseConfigBackorders() != 0);
    	} else {
    		return (($stockItem->getBackorders() != \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO) && !$stockItem->getUseConfigBackorders());
    	}
    	
    	return false;
    }
    
    protected function isBackorderQtyOk()
    {
    	$stockItem = $this->getStockItem();
    	
    	$minQty = $stockItem->getMinQty();
    	$qty = $stockItem->getQty();
    	
    	return ($qty < $minQty && $this->isBackorderEnabled());
    }
    
    /**
     * Check if we should show due_date field
     * @return boolean
     */
    public function showDueDate()
    {
        return $this->backorderProductHelper->isProductBackorder();
    }
}
