<?php
/**
 * Copyright © 2016 Sectionio. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sectionio\Metrics\Model\ResourceModel\Application;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Sectionio\Metrics\Model\Application',
            'Sectionio\Metrics\Model\ResourceModel\Application'
        );
    }
}
