<?php

namespace Acidgreen\SploshBackorder\Helper;

use Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\ItemFactory as StockzoneItemResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Product
{

    /**
     * @var boolean
     */
    protected $isProductBackorder;
	
	/**
	 * @var StockzoneItemResource
	 */
	protected $stockzoneItemResource;

	/**
	 * @var StoreManagerInterface
	 */
	protected $_storeManager;
	
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(
    	StockzoneItemResource $stockzoneItemResource,
    	StoreManagerInterface $storeManager,
    	Registry $coreRegistry
    ) {
    	$this->stockzoneItemResource = $stockzoneItemResource;
    	$this->_storeManager = $storeManager;
    	$this->_coreRegistry = $coreRegistry;
    }

    public function isProductBackorder()
    {
        $product = $this->_coreRegistry->registry('product');
        
        if (!isset($this->isProductBackorder))
        	$this->isProductBackorder = false;
        
        if (!empty($product)) {
            $this->isProductBackorder = !empty($product->getForceBackorder());
        }
        
        return $this->isProductBackorder;
    }

}
