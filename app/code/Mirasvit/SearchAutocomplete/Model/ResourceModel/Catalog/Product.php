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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Model\ResourceModel\Catalog;


class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product 
{
    /**
     * @var array
     */
    private $productIds = [];

    /**
     * {@inheritDoc}
     */
    public function prepareSelectStatement(\Magento\Framework\DB\Select $select)
    {
        $select->columns('sku');
    	$select->where('e.entity_id IN (?)', $this->productIds);
        return $select;
    }
    
    /**
     * @param array $productIds
     * @return void
     */
    public function setProductIds(array $productIds) 
    {
        $this->productIds = $productIds;
    }
}