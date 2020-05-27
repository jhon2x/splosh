<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Carrier;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreResolver;
use MageWorx\ShippingRules\Helper\Data as Helper;

/**
 * Class AbstractModel
 */
abstract class AbstractModel extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * Columns which will be ignored during import/export process
     *
     * @see \MageWorx\ShippingRules\Model\Carrier\AbstractModel::getIgnoredColumnsForImportExport()
     */
    const IMPORT_EXPORT_IGNORE_COLUMNS = [
        'id'
    ];
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StoreResolver
     */
    protected $storeResolver;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * AbstractModel constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param StoreResolver $storeResolver
     * @param Helper $helper
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        StoreResolver $storeResolver,
        Helper $helper,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager  = $storeManager;
        $this->storeResolver = $storeResolver;
        $this->helper        = $helper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get columns which should be removed during import\export process
     *
     * @return array
     */
    public static function getIgnoredColumnsForImportExport()
    {
        return static::IMPORT_EXPORT_IGNORE_COLUMNS;
    }

    /**
     * Convert current object to string
     *
     * @param string $format
     * @return mixed|string
     */
    public function toString($format = '')
    {
        if (empty($format)) {
            $result = implode(', ', $this->getData());
        } else {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach ($matches[1] as $var) {
                $method = 'get' . implode('', array_map('ucfirst', explode('_', $var)));
                if (method_exists($this, $method)) {
                    $data = $this->{$method}();
                } else {
                    $data = $this->getData($var);
                }

                // Format array values
                if (is_array($data)) {
                    $formattedData = implode(',', $data);
                } else {
                    $formattedData = $data;
                }

                $format = str_replace('{{' . $var . '}}', $formattedData, $format);
            }
            $result = $format;
        }

        return $result;
    }

    /**
     * Get Carrier label by specified store
     *
     * @param \Magento\Store\Model\Store|int|bool|null $store
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLabel($store = null)
    {
        if ($this->_appState->getAreaCode() === Area::AREA_ADMINHTML) {
            return false;
        }

        $storeId = $this->resolveStoreId($store);
        $labels  = (array)$this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Resolves the current store id according an area where it was called or using $store argument
     *
     * @param null $store
     * @return int|string|null
     */
    public function resolveStoreId($store = null)
    {
        try {
            if (!$store) {
                $storeId = $this->storeResolver->getCurrentStoreId();
            } elseif ($store instanceof \Magento\Store\Api\Data\StoreInterface) {
                $storeId = $store->getId();
            } else {
                $storeId = $this->storeManager->getStore($store)->getId();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noSuchEntityException) {
            $storeId = null;
        }

        return $storeId;
    }
}