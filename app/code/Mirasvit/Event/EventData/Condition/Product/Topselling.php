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

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Mirasvit\Event\Api\Data\Event\InstanceEventInterface;
use Mirasvit\Event\EventData\ProductData;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

class Topselling extends AbstractCondition
{
    const RATE = 'rate';

    /**
     * @var Yesno
     */
    private $yesnoSource;
    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    public function __construct(
        CollectionFactory $itemCollectionFactory,
        Yesno $yesnoSource,
        Context $context,
        array $data = []
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->yesnoSource = $yesnoSource;

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
            . __('Product is one of %1 top selling products is %2',
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
        $limit  = (int) $this->getValue();
        $items  = $this->getItemsId($model->getData(InstanceEventInterface::PARAM_STORE_ID), $limit);
        $result = in_array($model->getData(ProductData::ID), $items);

        return $this->getData('attribute') ? $result : !$result; // inverse result if attribute set to 0 - "No"
    }

    /**
     * Get top selling item IDs filtered by passed $storeId and limited with $limit.
     *
     * @param int $storeId
     * @param int $limit
     *
     * @return int[]
     */
    private function getItemsId($storeId, $limit)
    {
        $items = $this->itemCollectionFactory->create();

        $items->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                self::RATE      => new \Zend_Db_Expr('COUNT(*)'),
                ProductData::ID => 'product_id'
            ])
            ->where('parent_item_id IS NULL AND store_id = ?', $storeId)
            ->group('product_id')
            ->limit($limit);

        $ids = $items->getColumnValues(ProductData::ID);

        return $ids;
    }
}
