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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Index\Magento\Catalog\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;

class Index extends AbstractIndex
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        $dataMappers
    ) {
        $this->eavConfig = $eavConfig;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Magento / Attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'magento_catalog_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'label' => __('Attribute value (option)'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'value';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        //        $this->setRecentId($this->getIndexId());
        $ids = $this->context->getSearcher()->getMatchedIds();

        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        $attribute = $this->eavConfig->getAttribute(
            'catalog_product',
            $this->getModel()->getProperty('attribute')
        );

        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                if (in_array($option['value'], $ids)) {
                    $collection->addItem(
                        new DataObject($option)
                    );
                }
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = new Collection(new EntityFactory($this->context->getObjectManager()));

        if ($lastEntityId) {
            return $collection;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $this->getModel()->getProperty('attribute'));
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $collection->addItem(
                    new DataObject($option)
                );
            }
        }

        return $collection;
    }
}
