<?php

namespace Acidgreen\Wishlist\Block\Wishlist;

class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        // \Magento\Framework\App\Http\Context $httpContext,
        // \Magento\Customer\Model\Registration $registration,
        array $data = []
    ) {
        parent::__construct($context, $data);
        // $this->httpContext = $httpContext;
        // $this->_registration = $registration;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_urlBuilder->getUrl('wishlist/index/index');
    }
    /**
     * @return string
     */
    public function getLabel()
    {
        return ' ';
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
