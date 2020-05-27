<?php

namespace Splosh\SalesRep\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Search extends Template implements BlockInterface
{
    const PARAM_TITLE       = 'title';
    const PARAM_PLACEHOLDER = 'placeholder';
    const PARAM_CSS_STYLES  = 'css_styles';
    const PARAM_ERROR_MSG   = 'error_msg';
    const PARAM_ERROR_MSG_CLASS = 'error_msg_class';
    const PARAM_ITEM_CLASS  = 'item_class';
    const PARAM_ITEM_IMAGE_CLASS = 'item_image_class';
    const AJAX_URL          = 'splosh_salesrep/location/search';

    /**
     * @var string
     */
    protected $_template = 'widget/search.phtml';

    /**
     * Search constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::PARAM_TITLE);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl(self::AJAX_URL);
    }

    /**
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function getPlaceholderText()
    {
        return $this->getData(self::PARAM_PLACEHOLDER);
    }

    /**
     * @return mixed|string
     */
    public function getErrorMsg()
    {
        return $this->getData(self::PARAM_ERROR_MSG);
    }

    /**
     * @return mixed|string
     */
    public function getErrorMsgClass()
    {
        return $this->getData(self::PARAM_ERROR_MSG_CLASS);
    }

    /**
     * @return mixed|string
     */
    public function getItemClass()
    {
        return $this->getData(self::PARAM_ITEM_CLASS);
    }

    /**
     * @return mixed|string
     */
    public function getItemImageClass()
    {
        return $this->getData(self::PARAM_ITEM_IMAGE_CLASS);
    }
}
