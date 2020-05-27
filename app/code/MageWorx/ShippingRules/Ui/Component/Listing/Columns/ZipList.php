<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ZipList
 */
class ZipList extends Column
{
    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    private $helper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \MageWorx\ShippingRules\Model\Config\Source\Locale\AllRegions $sourceRegions
     * @param mixed[] $components
     * @param mixed[] $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \MageWorx\ShippingRules\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param mixed[] $dataSource
     * @return mixed[]
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param mixed[] $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content = '';

        if (!empty($item['zip'])) {
            $origZips = explode(',', $item['zip']);
        }

        if (empty($origZips)) {
            return __('No Zip Code');
        }

        if (!is_array($origZips)) {
            $origZips = [$origZips];
        }

        $excludedZips = [];
        $includedZips = [];
        foreach ($origZips as $zip) {
            if (stripos($zip, '!') !== false) {
                $excludedZips[] = str_replace('!', '', $zip);
            } else {
                $includedZips[] = $zip;
            }
        }

        if (!empty($includedZips)) {
            $content .= __('Include:') . '<br />' . implode('; ', $includedZips) . '<br />';
        }

        if (!empty($excludedZips)) {
            $content .= __('Exclude:') . '<br />' . implode('; ', $excludedZips) . '<br />';
        }

        return $content;
    }
}