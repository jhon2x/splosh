<?php

namespace Acidgreen\SploshBackorder\Block\Product\View\Type;

use Acidgreen\SploshBackorder\Helper\Product as BackorderProductHelper;

// class Simple extends CoreSimpleBlock
class Simple extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Acidgreen\SploshBackorder\Helper\Product
     */
    protected $backorderProductHelper;

    /**
     * @param \Acidgreen\SploshBackorder\Helper\Product $backorderProductHelper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
    	\Acidgreen\SploshBackorder\Helper\Product $backorderProductHelper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
    	$this->backorderProductHelper = $backorderProductHelper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }
    
    public function getProduct()
    {
    	return parent::getProduct();
    }

    public function isProductBackorder()
    {
        return $this->backorderProductHelper->isProductBackorder();
    }

}
