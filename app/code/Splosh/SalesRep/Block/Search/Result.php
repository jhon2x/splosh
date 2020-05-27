<?php

namespace Splosh\SalesRep\Block\Search;

use Magento\Framework\View\Element\Template;

class Result extends Template
{
    const DEFAULT_EMPTY_RESULT_TEXT = '<p class="result empty">
        No sales rep. matched your search. Please review your search query.
    </p>';

    /**
     * @var string
     */
    protected $_template = 'search/result.phtml';

    /**
     * Result constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getStaffResult()
    {
        return $this->getData('staff_result');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $staffResult = $this->getStaffResult();

        if (count($staffResult) <= 0) {
            return self::DEFAULT_EMPTY_RESULT_TEXT;
        }

        return parent::_toHtml();
    }
}