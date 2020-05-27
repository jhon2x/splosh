<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Ui\DataProvider\Rate;

use Magento\Framework\Data\Collection;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\Grid\RegularCollection as RealCollection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class ZipToFilterStrategy
 */
class ZipToFilterStrategy implements AddFilterToCollectionInterface
{
    /**
     * @param Collection|RealCollection $collection
     * @param string $field
     * @param null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        if (isset($condition['like'])) {
            $collection->joinZipsTables();
            $collection->getSelect()->where('`zdt`.`to` LIKE \'' . $condition['like'] . '\'');
        }
    }
}