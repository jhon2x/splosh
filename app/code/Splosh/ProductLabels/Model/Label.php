<?php

namespace Splosh\ProductLabels\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel;
use Magento\Framework\Model\Context;

/**
 * Class Label
 * @package Splosh\ProductLabels\Model
 */
class Label extends AbstractModel
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Label constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(\Splosh\ProductLabels\Model\ResourceModel\Label::class);
    }

    /**
     * @return array
     */
    public function labelStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }
}