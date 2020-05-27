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
 * @package   mirasvit/module-misspell
 * @version   1.0.32
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Misspell\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Mirasvit\Misspell\Helper\Text as TextHelper;
use Mirasvit\Misspell\Model\SuggestFactory;

class Query extends AbstractHelper
{
    /**
     * @var array
     */
    protected $fallbackResult = [];

    /**
     * @var array
     */
    protected $fallbackCombination = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Search\Model\Query
     */
    protected $query;

    /**
     * @var \Mirasvit\Misspell\Helper\Text
     */
    protected $text;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Mirasvit\Misspell\Model\SuggestFactory
     */
    protected $suggestFactory;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        UrlFactory $urlFactory,
        SuggestFactory $suggestFactory,
        TextHelper $textHelper,
        QueryCollectionFactory $queryCollectionFactory
    ) {
        $this->request = $context->getRequest();
        $this->query = $queryFactory->get();
        $this->text = $textHelper;
        $this->urlFactory = $urlFactory;
        $this->suggestFactory = $suggestFactory;
        $this->queryCollectionFactory = $queryCollectionFactory;

        parent::__construct($context);
    }

    /**
     * Query text
     *
     * @return string
     */
    public function getQueryText()
    {
        return strip_tags($this->query->getQueryText());
    }

    /**
     * Old query text (before misspell)
     *
     * @return string
     */
    public function getMisspellText()
    {
        return strip_tags($this->request->getParam('o'));
    }

    /**
     * Old query text (before fallback)
     *
     * @return string
     */
    public function getFallbackText()
    {
        return strip_tags($this->request->getParam('f'));
    }

    /**
     * Misspell Url
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getMisspellUrl($from, $to)
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'o' => $from])
            ->getUrl('*/*/*');
    }

    /**
     * Fallback Url
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getFallbackUrl($from, $to)
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'f' => $from])
            ->getUrl('*/*/*');
    }

    /**
     * Number results for specified search query
     *
     * @param string $queryText
     * @return int
     */
    public function getNumResults($queryText = null)
    {
        if ($queryText == null) {
            return $this->query->getNumResults();
        }

        $collection = $this->queryCollectionFactory->create()
            ->addFieldToFilter('query_text', trim($queryText));

        $query = $collection->getFirstItem();

        if ($query->getId()) {
            return $query->getNumResults();
        }

        return 1;
    }

    /**
     * Suggest
     *
     * @param string $text
     * @return string
     */
    public function suggest($text)
    {
        $result = false;

        $model = $this->suggestFactory->create()->load($text);

        $suggest = $model->getSuggest();

        if ($this->text->strtolower($text) != $this->text->strtolower($suggest)) {
            $result = $suggest;
        }

        return $result;
    }

    /**
     * Fallback
     *
     * @param string $text
     * @return string
     */
    public function fallback($text)
    {
        $arQuery = $this->text->splitWords($text);

        for ($i = 1; $i < count($arQuery); $i++) {
            $combinations = $this->fallbackCombinations($arQuery, $i);
            foreach ($combinations as $combination) {
                $newQuery = $text;
                foreach ($combination as $word) {
                    $newQuery = str_replace($word, '', $newQuery);

                    $cntResults = $this->getNumResults($newQuery);

                    if ($cntResults > 0) {
                        $newQuery = preg_replace('/\s{2,}/', ' ', $newQuery);

                        return trim($newQuery);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Fallback combinations
     *
     * @param array $array
     * @param int $choose
     *
     * @return array
     */
    protected function fallbackCombinations(array $array, $choose)
    {
        $n = count($array);
        $this->inner(0, $choose, $array, $n);

        return array_reverse($this->fallbackResult);
    }

    /**
     * Need add description
     *
     * @param int $start
     * @param int $choose
     * @param array $arr
     * @param int $n
     * @return void
     */
    protected function inner($start, $choose, $arr, $n)
    {
        if ($choose == 0) {
            array_push($this->fallbackResult, $this->fallbackCombination);
        } else {
            for ($i = $start; $i <= $n - $choose; ++$i) {
                array_push($this->fallbackCombination, $arr[$i]);
                $this->inner($i + 1, $choose - 1, $arr, $n);
                array_pop($this->fallbackCombination);
            }
        }
    }
}
