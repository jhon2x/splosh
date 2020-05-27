<?php
namespace Acidgreen\Checkout\Helper;

use Acidgreen\SploshBackorder\Model\Stockzone;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Checkout\Model\Session;
use Acidgreen\SploshBox\Model\Box;
use Magento\Framework\UrlInterface;

class Cart extends AbstractHelper
{
    const SITE_B2B_IDENTIFIER = 'b2b';

    /**
     * Collection of product IDs for querying later..
     * @var int[]
     */
    private $productIds;

	/**
	 * collection factory for stockzone items
	 * @var Stockzone\ItemFactory
	 */
    protected $stockzoneItemFactory;
    // store stockzone items on this block class
    // to be used for setStockzoneItem later at a child class
    // store otherProductData as well for same purpose

    /**
     * For retrieving the correct stockzone item
     * @var int
     */
    protected $websiteId;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var string
     */
    protected $websiteCode;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $sortedBoxItems = [];

    /**
     * @var \Acidgreen\SploshBackorder\Model\Stockzone\Item
     */
    protected $_stockZoneItem;

    /**
     * @var \Magento\Store\Api\Data\WebsiteInterface|null
     */
    protected $stockZoneSite = null;

    /**
     * @var \Acidgreen\SploshBackorder\Model\Stockzone
     */
    protected $_stockZone;

    /**
     * @var array
     */
    protected $backOrderItems = [];

    /**
     * @var array
     */
    protected $boxes;

    /**
     * @var Carton
     */
    protected $cartonCheckoutHelper;

    /**
     * @var \Acidgreen\SploshBox\Model\Box
     */
    protected $_sploshBox;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Cart constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Stockzone\ItemFactory $stockzoneItemFactory
     * @param ProductFactory $productFactory
     * @param Session $_checkoutSession
     * @param Carton $cartonCheckoutHelper
     * @param Box $_sploshBox
     * @param Stockzone $_stockZone
     * @param Stockzone\ItemFactory $_stockZoneItem
     * @param UrlInterface $urlBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Stockzone\ItemFactory $stockzoneItemFactory,
    	ProductFactory $productFactory,
        Session $_checkoutSession,
        Carton $cartonCheckoutHelper,
        Box $_sploshBox,
        Stockzone $_stockZone,
        Stockzone\ItemFactory $_stockZoneItem,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);

        $this->_storeManager = $storeManager;
        $this->stockzoneItemFactory = $stockzoneItemFactory;
        $this->websiteCode = $this->_storeManager->getWebsite()->getCode();
        $this->websiteId = $this->_storeManager->getWebsite()->getId();
        $this->productFactory = $productFactory;
        $this->_checkoutSession = $_checkoutSession;
        $this->cartonCheckoutHelper = $cartonCheckoutHelper;
        $this->_sploshBox = $_sploshBox;
        $this->_stockZone = $_stockZone;
        $this->_stockZoneItem = $_stockZoneItem;
        $this->urlBuilder = $urlBuilder;
    }

    /**
    * Determines if site is B2B according to identifier
    * @param string websiteCode
    * @return bool
    */
    public function isSiteB2b($websiteCode = null) {
        if (!$websiteCode) {
            $websiteCode = $this->websiteCode;
        }
        return (stripos(strtolower($websiteCode), self::SITE_B2B_IDENTIFIER) !== false);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * @throws \Exception
     */
    public function verifyBoxes()
    {
        $boxArray = array();
        $unmixedBoxes = array();

        foreach ($this->getQuote()->getItemsCollection() as $_item) {
            /** @var \Magento\Catalog\Model\Product $itemProduct */
            $itemProduct = $_item->getProduct();

            /** @var \Magento\Quote\Model\Quote\Item $_item */
            $productBox = $this->_sploshBox->getCollection()
                ->addFieldToFilter('box_type', $itemProduct->getExoCtnSize())
                ->load()
                ->getFirstItem();

            $productBoxId = $productBox->getId();

            if (!$productBoxId || !empty($itemProduct->getForceBackorder())) {
                continue;
            }

            if ($productBox->getIsMixedBox()) {
                $boxArray[$productBoxId]['multiQty'] = $productBox->getMultiQty();
                $boxArray[$productBoxId]['boxType'] = $productBox->getBoxType();
                $boxArray[$productBoxId]['quoteTotal'] = (isset($boxArray[$productBoxId]['quoteTotal'])) ?
                    $boxArray[$productBoxId]['quoteTotal'] + $_item->getQty() :
                    $_item->getQty();
            } else {
                $unmixedBoxes[$itemProduct->getSku()]['multiQty'] = $productBox->getMultiQty();
                $unmixedBoxes[$itemProduct->getSku()]['boxType'] = $productBox->getBoxType();
                $unmixedBoxes[$itemProduct->getSku()]['quoteTotal'] = (isset($unmixedBoxes[$itemProduct->getSku()]['quoteTotal'])) ?
                    $unmixedBoxes[$itemProduct->getSku()]['quoteTotal'] + $_item->getQty() :
                    $_item->getQty();
            }
        }

        foreach ($boxArray as $boxId => $box) {
            if ($box['quoteTotal'] % $box['multiQty'] != 0) {
                throw new \Exception('A mixed box is incomplete: ' . $box['boxType'] . ' BoxMultiQty is ' . $box['multiQty'] . ' with items of that box type total : ' . $box['quoteTotal']);
            }
        }

        foreach ($unmixedBoxes as $sku => $box) {
            if ($box['quoteTotal'] % $box['multiQty'] != 0) {
                throw new \Exception('An unmixed box is incomplete: ' . $box['boxType'] . ' BoxMultiQty is ' . $box['multiQty'] . ' with item ' . $sku . ' of that box total : ' . $box['quoteTotal']);
            }
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSortedBoxItems() {
        if (!empty($this->sortedBoxItems)) {
            return $this->sortedBoxItems;
        }
        $this->sortedBoxItems = $this->sortBoxItems();
        return $this->sortedBoxItems;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function sortBoxItems()
    {
        $boxArray = array(
            'mixed' => array(),
            'unmixed' => array(),
            'backorders' => array(),
            'no_box' => array()
        );

        $productStoreAttributes = $this->cartonCheckoutHelper->getProductStoreAttributes($this->getQuote());

        foreach($this->getQuote()->getAllVisibleItems() as $_item) {

            $itemProduct = $_item->getProduct();

            $productBox = $this->_sploshBox->getCollection()
                ->addFieldToFilter('box_type', $itemProduct->getExoCtnSize())
                ->load()
                ->getFirstItem();

            if (!empty($itemProduct->getForceBackorder())) {
                $boxArray['backorders']['backorders']['items'][] = $_item;
            } else if (!$productBox->getId() || empty($productStoreAttributes[$_item->getProductId()])) {
                $boxArray['no_box']['no_box']['items'][] = $_item;
            } else if ($productBox->getIsMixedBox()) {
                $boxArray['mixed'][$productBox->getBoxType()]['items'][] = $_item;
            } else {
                $boxArray['unmixed'][$productBox->getBoxType()]['items'][] = $_item;
            }
        }

        $this->boxes = $boxArray;
        $this->removeEmpty();
        $this->addValidation();

        return $this->boxes;
    }

    /**
     * Remove Boxes empty index
     */
    private function removeEmpty() {
        foreach ($this->boxes as $key => $box) {
            if (empty($box)) {
                unset($this->boxes[$key]);
            }
        }
    }

    /**
     * Add Validation
     */
    private function addValidation()
    {
        $this->_checkoutSession->setData('cart_has_error', false);
        foreach ($this->boxes as $box_mix => $box) {
            if ($box_mix == 'mixed' || $box_mix == 'unmixed') {
                foreach ($box as $box_type => $box_content) {
                    $productBox = $this->_sploshBox->getCollection()
                        ->addFieldToFilter('box_type', $box_type)
                        ->load()
                        ->getFirstItem();
                    $boxTotal = 0;
                    $boxMultiQty = ($productBox->getMultiQty()) ? $productBox->getMultiQty() : 1;

                    if ($box_mix == 'mixed') {
                        foreach ($box_content['items'] as $_item) {
                            $boxTotal = $boxTotal + $_item->getQty();
                        }

                        $boxQtyRemaining = $boxTotal % $boxMultiQty;
                        if ($boxQtyRemaining != 0) {
                            $boxQtyRemaining = $boxMultiQty - $boxQtyRemaining;
                            $this->_checkoutSession->setData('cart_has_error', true);

                            $errorMessage = '<ul>';
                            $errorMessage .= '<li>' . $box_type . ' carton must be ordered in ' . $boxMultiQty . '\'s</li>';
                            $errorMessage .= '<li>Please add ' . $boxQtyRemaining . ' more to complete the carton</li>';
                            $errorMessage .= '<li>You may adjust the quantities of the existing items or add ' . $this->getCatalogSearchCartonLink($box_type) . ' to your order</li>';
                            $errorMessage .= '</ul>';

                            $this->boxes[$box_mix][$box_type]['validation'] = array(
                                'status' => 'error',
                                'message' => __($errorMessage)
                            );
                        } else {
                            $boxShipments = (int)ceil($boxTotal / $boxMultiQty);
                            $this->boxes[$box_mix][$box_type]['validation'] = array(
                                'status' => 'success',
                                'message' => __('We will ship you ' . $boxShipments . ' ' . $box_type . ' cartons containing the products below.')
                            );
                        }
                    } else {
                        foreach ($box_content['items'] as $key => $_item) {
                            $boxQtyRemaining = $_item->getQty() % $boxMultiQty;

                            if ($boxQtyRemaining != 0) {
                                $boxQtyRemaining = $boxMultiQty - $boxQtyRemaining;
                                $this->_checkoutSession->setData('cart_has_error', true);

                                $errorMessage = '<ul>';
                                $errorMessage .= '<li>' . $box_type . ' carton must be ordered in ' . $boxMultiQty . '\'s</li>';
                                $errorMessage .= '<li>Please add ' . $boxQtyRemaining . ' more to complete the carton</li>';
                                $errorMessage .= '<li>You may adjust the quantities of the existing items or add ' . $this->getCatalogSearchCartonLink($box_type) . ' to your order</li>';
                                $errorMessage .= '</ul>';

                                $this->boxes[$box_mix][$box_type]['validation'][$key] = array(
                                    'status' => 'error',
                                    'message' => __($errorMessage)
                                );
                            } else {
                                $boxShipments = (int)ceil($_item->getQty() / $boxMultiQty);
                                $this->boxes[$box_mix][$box_type]['validation'][$key] = array(
                                    'status' => 'success',
                                    'message' => __('We will ship you ' . $boxShipments . ' ' . $box_type . ' cartons containing the products below.')
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockZoneItems() {
        if (!empty($this->backOrderItems)) {
            return $this->backOrderItems;
        }

        if (!$this->stockZoneSite) {
            $this->stockZoneSite = $this->_stockZone->getStockzoneByWebsite($this->_storeManager->getWebsite());
        }

        $stockZoneItems = $this->_stockZoneItem->getCollection()
            ->addFieldToFilter('splosh_stockzone_id', $this->stockZoneSite->getId())
            ->addFieldToFilter('backorders', array('1', '2'));

        foreach ($stockZoneItems as $item) {
            $this->backOrderItems[] = $item->getSku();
        }

        return $this->backOrderItems;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOtherProductCollectionData()
    {
        $collection = $this->productFactory->create()->getCollection();
        $collection->addAttributeToSelect(['exo_due_date', 'force_backorder'], 'inner')
            ->addFieldToFilter('entity_id', ['in' => $this->getProductIds()])
            ->load(false, true);

        $otherProductsData = [];

        foreach ($collection as $product) {
            $otherProductsData[$product->getId()] = $product;
        }

        return $otherProductsData;

    }

    /**
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCartStockzoneItems()
    {
        /** @var \Acidgreen\SploshBackorder\Model\ResourceModel\Stockzone\Item $stockzoneItemResource */
        $stockzoneItemResource = $this->stockzoneItemFactory->create()->getResource();
        /** @var array|boolean $cartItems */
        $cartItems = $stockzoneItemResource->getCartItems($this->getProductIds(), $this->websiteId);

        return $cartItems;
    }

    /**
     * @return array|int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductIds()
    {
        if (empty($this->productIds)) {
            $items = $this->getQuote()->getAllVisibleItems();

            $productIds = [];
            foreach ($items as $item) {
                array_push($productIds, $item->getProductId());
            }
            $this->productIds = $productIds;
        }
        return $this->productIds;
    }

    private function getCatalogSearchCartonLink($box_type)
    {
        return '<a href="'.$this->getCatalogSearchCartonUrl($box_type).'">'.__('other applicable items').'</a>';
    }

    private function getCatalogSearchCartonUrl($box_type)
    {
        return $this->urlBuilder->getUrl('catalogsearch/result', ['_query' => [
            'q' => $box_type,
            'exo_ctn_size' => $box_type
        ]]);
    }

    /**
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cartHasError() {
        $this->getSortedBoxItems();
        if ($this->_checkoutSession->getData('cart_has_error') === null) {
            return false;
        }
        return $this->_checkoutSession->getData('cart_has_error');
    }

    /**
     * Set cart has error in session
     */
    public function setCartHasError() {
        $this->_checkoutSession->setData('cart_has_error', true);
    }
}
