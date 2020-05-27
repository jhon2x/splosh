<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// namespace Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier;
namespace Acidgreen\SploshBackorder\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

use Magento\CatalogInventory\Model\Source\Backorders as BackorderOptions;
use Acidgreen\SploshBackorder\Model\StockzoneFactory;
use Acidgreen\SploshBackorder\Model\StockzoneRegistry;
use Acidgreen\SploshBackorder\Model\Stockzone\ItemFactory as StockzoneItemFactory;

/**
 * Data provider for advanced inventory form
 */
class BackorderInventory extends AbstractModifier
{

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockzoneFactory
     */
    private $stockzoneFactory;

    /**
     * @var StockzoneRegistry
     */
    private $stockzoneRegistry;

    /**
     * @var StockzoneItemFactory
     */
    private $stockzoneItemFactory;

    /**
     * @var Stockzone[]
     */
    private $stockzones;

    /**
     * @var BackorderOptions
     */
    private $backorderOptions;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param StockRegistryInterface $stockRegistry
     * @param ArrayManager $arrayManager
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        LocatorInterface $locator,
        /*
        StockRegistryInterface $stockRegistry,
        ArrayManager $arrayManager,
        StockConfigurationInterface $stockConfiguration,
         */
        StockzoneFactory $stockzoneFactory,
        StockzoneRegistry $stockzoneRegistry,
        StockzoneItemFactory $stockzoneItemFactory,
        BackorderOptions $backorderOptions
    ) {
        $this->locator = $locator;
        /*
        $this->stockRegistry = $stockRegistry;
        $this->arrayManager = $arrayManager;
        $this->stockConfiguration = $stockConfiguration;
         */
        $this->stockzoneFactory = $stockzoneFactory;
        $this->stockzoneRegistry = $stockzoneRegistry;
        $this->stockzoneItemFactory = $stockzoneItemFactory;
        $this->backorderOptions = $backorderOptions;
        
    }

    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        if (!empty($model))
            $this->initStockzoneItems($model->getSku());

        // get stockzone items and load them into the inputs...
        if (!empty($this->stockzoneItems)) {
            foreach ($this->stockzoneItems as $item) {
                $data[$model->getId()]['product']['stockzone_data]['.$item->getSploshStockzoneId().'][qty'] = $item->getQty();
                $data[$model->getId()]['product']['stockzone_data]['.$item->getSploshStockzoneId().'][backorders'] = $item->getBackorders();
            }
        }
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->initStockzones();
        $this->prepareMeta();

        return $this->meta;
    }

    private function prepareMeta()
    {
        $ctr = 0;

        $this->meta['advanced_inventory_modal']['children']['stockzone_data'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'fieldset',
                            'label' => 'Stock Zone Data (for Backorders)',
                            'sortOrder' => 100
                        ]
                    ]
                ],
                'children' => []
        ];

        foreach ($this->stockzones as $stockzone) {
            // $stockzoneKey = 'stockzone_inventory_'.$stockzone->getId();
            /*
            $stockzoneKey = 'stockzone_data]['.$stockzone->getDescription().'][qty';
            $meta = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'scopeLabel' => '[WEBSITE]',
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'visible' => 1,
                            'label' => __('Qty for Stockzone '.$stockzone->getId())
                        ]
                    ]
                ]
            ];
            $this->meta['advanced_inventory_modal']['children']['stockzone_data']['children'][$stockzoneKey] = $meta;
             */
            $stockzoneKey = 'stockzone_data]['.$stockzone->getId().'][backorders';
            $meta = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'scopeLabel' => '[WEBSITE]',
                            'formElement' => 'select',
                            'componentType' => 'field',
                            'visible' => 1,
                            'options' => $this->backorderOptions->toOptionArray(),
                            'label' => __('Backorders '.$stockzone->getDescription())
                        ]
                    ]
                ]
            ];
            $this->meta['advanced_inventory_modal']['children']['stockzone_data']['children'][$stockzoneKey] = $meta;
            // ^ fieldset could be better?
        }

    }

    private function initStockzones()
    {
        $this->stockzones = $this->stockzoneRegistry->getStockzones();
    }

    private function initStockzoneItems($sku)
    {
        $this->stockzoneItems = $this->stockzoneItemFactory->create()->getProductStockzoneItems($sku);
    }

}
