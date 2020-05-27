<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\ResourceModel\Region\Filter;

use MageWorx\ShippingRules\Model\ResourceModel\Region\Collection as RegionCollection;

/**
 * Class Collection
 */
class Collection extends RegionCollection
{
    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        return $this;
    }
}
