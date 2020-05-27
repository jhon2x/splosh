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
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin;

use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Block\Result;
use Mirasvit\Search\Model\Config;

class HighlightPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(
        Config $config,
        QueryFactory $queryFactory
    ) {
        $this->config       = $config;
        $this->queryFactory = $queryFactory;
    }

    /**
     * @param Result $block
     * @param string $html
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function afterToHtml(
        Result $block,
        $html
    ) {
        if (!$this->config->isHighlightingEnabled()) {
            return $html;
        }

        $html = $this->highlight(
            $html,
            $this->queryFactory->get()->getQueryText()
        );

        return $html;
    }

    /**
     * @param string $html
     * @param string $query
     * @return string
     */
    public function highlight($html, $query)
    {
        if (strlen($query) < 3) {
            return $html;
        }

        $query = $this->removeSpecialChars($query);
        preg_match_all("/[\$\/\|\-\w\d\s]*" . $query . "[\$\/\|\-\w\d\s]*<\s*\/\s*a/is", $html, $matches);

        foreach ($matches[0] as $match) {
            $html = $this->_highlight($html, $match, $query);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $match
     * @param string $query
     * @return string
     */
    private function _highlight($html, $match, $query)
    {
        $replacement = substr_replace($match, '<span class="mst-search__highlight">', stripos($match, $query), 0);
        $replacement = substr_replace($replacement, '</span>', stripos($replacement, $query) + strlen($query), 0);

        return str_replace($match, $replacement, $html);
    }

    /**
     * @param string $query
     * @return string
     */
    public function removeSpecialChars($query)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = ' ';

        return preg_replace($pattern, $replace, $query);
    }
}
