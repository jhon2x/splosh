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



namespace Mirasvit\Search\Ui\ScoreRule\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;
use Mirasvit\Search\Model\ScoreRule;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var ScoreRuleInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $title = $item->getTitle();
            list($sign, $number) = explode('|', $item->getScoreFactor());
            $class = $sign == '*' || $sign == '+' ? 'plus' : 'minus';
            if ($sign == '*') {
                $sign = '×';
            } elseif ($sign == '/') {
                $sign = '÷';
            }

            $title .= "<div class='$class'><span>$sign</span><i>$number</i></div>";

            $conditions = [];
            $getConditions = strpos($item->getRule()->getConditions()->asStringRecursive(), PHP_EOL);
            $getPostConditions = strpos($item->getRule()->getPostConditions()->asStringRecursive(), PHP_EOL);

            if ($getConditions !== false && $getConditions > 0) {
                $conditions[] = $item->getRule()->getConditions()->asStringRecursive();
            }

            if ($getPostConditions !== false && $getPostConditions > 0) {
                $conditions[] = $item->getRule()->getPostConditions()->asStringRecursive();
            }

            $data = [
                ScoreRuleInterface::ID          => $item->getId(),
                ScoreRuleInterface::TITLE       => $title,
                ScoreRuleInterface::IS_ACTIVE   => $item->isActive(),
                ScoreRuleInterface::ACTIVE_FROM => $item->getActiveFrom(),
                ScoreRuleInterface::ACTIVE_TO   => $item->getActiveTo(),
                ScoreRuleInterface::STORE_IDS   => implode(',', $item->getStoreIds()),
                'conditions'                    => implode(PHP_EOL . PHP_EOL, $conditions),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
