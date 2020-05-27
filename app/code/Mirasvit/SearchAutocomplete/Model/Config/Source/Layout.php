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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchAutocomplete\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\SearchAutocomplete\Model\Config;

class Layout implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::LAYOUT_1_COLUMN,
                'label' => __('1 Column'),
            ],
            [
                'value' => Config::LAYOUT_2_COLUMNS,
                'label' => __('2 Columns'),
            ],
        ];
    }
}
