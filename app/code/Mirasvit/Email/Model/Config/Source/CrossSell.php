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
 * @package   mirasvit/module-email
 * @version   2.1.28
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Email\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CrossSell implements ArrayInterface
{
    const MAGE_CROSS = 'mage_cross';
    const MAGE_RELATED = 'mage_related';
    const MAGE_UPSELLS = 'mage_upsells';

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            self::MAGE_CROSS   => __('Cross-sell products'),
            self::MAGE_RELATED => __('Related products'),
            self::MAGE_UPSELLS => __('Upsell products'),
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = self::toArray();
        $result = [];

        foreach ($options as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value,
            ];
        }

        return $result;
    }
}
