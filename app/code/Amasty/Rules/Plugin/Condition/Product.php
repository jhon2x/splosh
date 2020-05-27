<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Plugin\Condition;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Additional attr for validator.
 */
class Product
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Amasty\Rules\Helper\Data
     */
    private $rulesDataHelper;

    /**
     * @var \Amasty\Rules\Model\ConfigModel
     */
    private $configModel;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Rules\Helper\Data $rulesDataHelper,
        \Amasty\Rules\Model\ConfigModel $configModel
    ) {
        $this->productRepository = $productRepository;
        $this->rulesDataHelper = $rulesDataHelper;
        $this->configModel = $configModel;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return \Magento\Rule\Model\Condition\Product\AbstractProduct
     */
    public function afterLoadAttributeOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
    ) {
        $attributes = [
            'quote_item_sku' => __('Custom Options SKU'),
            'quote_item_row_total_incl_tax' => __('Row total in cart with tax')
        ];
        if ($this->configModel->getOptionsValue()) {
            $attributes['quote_item_value'] = __('Custom Options Values');
        }

        $subject->setAttributeOption(array_merge($subject->getAttributeOption(), $attributes));

        return $subject;
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function beforeValidate(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        if ($object->getProduct() instanceof \Magento\Catalog\Model\Product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $object->getProduct();
        } else {
            try {
                $product = $this->productRepository->getById($object->getProductId());
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }

        if ($product && $product->getTypeId() !== 'skip') {
            if ($this->configModel->getOptionsValue()) {
                $options = $product->getTypeInstance()->getOrderOptions($product);
                $values = '';

                if (isset($options['options'])) {
                    foreach ($options['options'] as $option) {
                        $values .= '|' . $option['value'];
                    }
                }

                $product->setQuoteItemValue($values);
            }

            $product->setQuoteItemRowTotalInclTax($object->getBaseRowTotalInclTax());
            $product->setQuoteItemSku($object->getSku());
            $object->setProduct($product);
        }
    }
}
