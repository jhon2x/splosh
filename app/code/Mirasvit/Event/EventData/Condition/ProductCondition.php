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



namespace Mirasvit\Event\EventData\Condition;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Event\Api\Data\EventDataInterface;
use Mirasvit\Event\Api\Repository\AttributeRepositoryInterface;
use Mirasvit\Event\EventData\ProductData;

class ProductCondition extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @inheritDoc
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->objectManager = ObjectManager::getInstance();

        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource,
            $attrSetCollection, $localeFormat, $data);
    }

    /**
     * Get EventData instance associated with this condition.
     *
     * @return EventDataInterface
     */
    public function getEventData()
    {
        return $this->objectManager->get($this->getEventDataClass());
    }

    /**
     * Get EventData class associated with this condition.
     *
     * @return string
     */
    public function getEventDataClass()
    {
        return ProductData::class;
    }

    /**
     * @inheritDoc
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);

        // Add attributes added to EventData manually
        foreach ($this->getEventData()->getAttributes() as $attribute) {
            if ($attribute->getType()) {
                $attributes[$attribute->getCode()] = $attribute->getLabel();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'qty':
                return 'numeric';
        }

        return parent::getInputType();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(AbstractModel $dataObject)
    {
        $attribute = $this->attributeRepository->get($this->getAttribute(), $this->getEventData());
        // attributes added manually have "Type", values for these attributes can be retrieved from model
        if ($attribute && ($value = $attribute->getValue($dataObject)) !== null) {
            return $this->validateAttribute($value);
        }

        $model = $dataObject->getData($this->getEventData()->getIdentifier());

        return parent::validate($model);
    }
}
