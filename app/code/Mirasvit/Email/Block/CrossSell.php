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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Block;

use Magento\Catalog\Block\Product\ListProduct;

class CrossSell extends ListProduct
{
    /**
     * {@inheritdoc}
     */
    public function _toHtml()
    {
        $this->setArea('frontend');
        $this->setTemplate('cross_sell.phtml');

        return parent::_toHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->_productCollection;
    }

    protected function _beforeToHtml()
    {
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );

        $this->_getProductCollection()->load();

        return $this;
    }
}
