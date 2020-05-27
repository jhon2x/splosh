<?php

namespace Acidgreen\Custom503\Block;

/**
 * Block class to generate custom 503 page response
 */
class Custom503 extends \Magento\Framework\View\Element\Template
{
    const CONFIG_WEB_DEFAULT_CMS_503_PAGE = 'web/default/cms_503_page';

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context, $data);
    }

    public function getContent()
    {
        // $identifier = 'maintenance-page';
        // get config from web/default/cms_503_page
        $identifier = $this->_scopeConfig->getValue(self::CONFIG_WEB_DEFAULT_CMS_503_PAGE);
        $page = $this->pageFactory->create();
        $page->setStoreId($this->_storeManager->getStore()->getId())->load($identifier, 'identifier');

        return $page->getContent();
    }
}
