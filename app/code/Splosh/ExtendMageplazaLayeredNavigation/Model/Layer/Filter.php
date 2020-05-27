<?php

namespace Splosh\ExtendMageplazaLayeredNavigation\Model\Layer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Filter extends \Mageplaza\LayeredNavigation\Model\Layer\Filter
{
    const CONFIG_CATALOG_SHOW_PRODUCT_COUNT = 'catalog/layered_navigation/display_product_count';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Filter constructor.
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($request);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return bool|mixed
     */
    public function isShowCounter($filter)
    {
        return $this->scopeConfig->getValue(self::CONFIG_CATALOG_SHOW_PRODUCT_COUNT);
    }
}