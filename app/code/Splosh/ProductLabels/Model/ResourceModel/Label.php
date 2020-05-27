<?php

namespace Splosh\ProductLabels\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Label
 * @package Splosh\ProductLabels\Model\ResourceModel
 */
class Label extends AbstractDb
{

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init('splosh_product_labels', 'label_id');
    }
}