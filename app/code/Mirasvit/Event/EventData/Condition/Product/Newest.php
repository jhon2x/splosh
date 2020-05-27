<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\EventData\Condition\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Event\EventData\ProductData;

class Newest extends AbstractCondition
{
    /**
     * @var Yesno
     */
    private $yesnoSource;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        Yesno $yesnoSource,
        Context $context,
        array $data = []
    ) {
        $this->yesnoSource = $yesnoSource;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($context, $data);

        $this->setData('type', self::class);
        $this->setData('attribute', 1); // default attribute
    }

    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        $this->setData('attribute_option', $this->yesnoSource->toArray());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __('Product is one of %1 most recently added products is %2',
                $this->getValueElementHtml(),
                $this->getAttributeElementHtml()
            )
            . $this->getRemoveLinkHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $result = false;
        $limit  = (int) $this->getValue();

        $this->searchCriteriaBuilder->addSortOrder(ProductInterface::CREATED_AT, SortOrder::SORT_DESC);
        $this->searchCriteriaBuilder->setPageSize($limit);

        $productList = $this->productRepository->getList($this->searchCriteriaBuilder->create());

        foreach ($productList->getItems() as $product) {
            if ($product->getId() === $model->getData(ProductData::ID)) {
                $result = true;
                break;
            }
        }

        return $this->getData('attribute') ? $result : !$result; // inverse result if attribute set to 0 - "No"
    }
}
