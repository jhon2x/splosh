<?php
namespace Acidgreen\Catalog\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class View extends \Magento\Catalog\Helper\Product\View
{
	/**
	 * Store manager
	 * @var StoreManagerInterface
	 */
	protected $storeManager;
	
	/**
	 * Logger
	 * @var LoggerInterface
	 */
	protected $logger;
	
	public function __construct(
		StoreManagerInterface $storeManager,
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        array $messageGroups = []
	) {
		$this->storeManager = $storeManager;
		$this->logger = $context->getLogger();
        parent::__construct(
            $context, 
            $catalogSession, 
            $catalogDesign, 
            $catalogProduct, 
            $coreRegistry, 
            $messageManager, 
            $categoryUrlPathGenerator
        );
		
	}
	
    public function prepareAndRender(ResultPage $resultPage, $productId, $controller, $params = null)
    {
        /**
         * Remove default action handle from layout update to avoid its usage during processing of another action,
         * It is possible that forwarding to another action occurs, e.g. to 'noroute'.
         * Default action handle is restored just before the end of current method.
         */
        $defaultActionHandle = $resultPage->getDefaultLayoutHandle();
        $handles = $resultPage->getLayout()->getUpdate()->getHandles();
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->getLayout()->getUpdate()->removeHandle($resultPage->getDefaultLayoutHandle());
        }

        if (!$controller instanceof \Magento\Catalog\Controller\Product\View\ViewInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Bad controller interface for showing product')
            );
        }
        // Prepare data
        $productHelper = $this->_catalogProduct;
        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }

        // Standard algorithm to prepare and render product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product is not loaded'));
        }
        
        /**
         * SPL-252 - redirect if $showOutOfStock disabled
         */
        $currentStoreCode = $this->storeManager->getStore()->getCode();
        $showOutOfStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $currentStoreCode
        );
        // SPL-285 - use force_backorder instead?
		// if (!$product->isSaleable() && !$showOutOfStock) {
        if ((!$product->isSaleable() && !$showOutOfStock) ||
            (!$product->isSaleable() && !$product->getForceBackorder() && preg_match("/".$currentStoreCode."/", "au_b2b_store_view,nz_b2b_store_view"))) {
			throw new \Acidgreen\Catalog\Exception\OutOfStockException(__('Product is Out of Stock.'));
		}

        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }

        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }

        $this->_eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);

        $this->_catalogSession->setLastViewedProductId($product->getId());

        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->addDefaultHandle();
        }

        $this->initProductLayout($resultPage, $product, $params);
        return $this;
    }
}
