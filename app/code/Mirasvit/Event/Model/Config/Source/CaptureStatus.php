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
 * @package   mirasvit/module-event
 * @version   1.2.27
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CaptureStatus implements ArrayInterface
{
    const STATUS_ON     = 0;
    const STATUS_OFF    = 1;
    const STATUS_OFF_EU = 2;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STATUS_ON,
                'label' => __('Yes')
            ],
            [
                'value' => self::STATUS_OFF,
                'label' => __('No')
            ],
            [
                'value' => self::STATUS_OFF_EU,
                'label' => __('* No for EU clients only')
            ],
        ];
    }
}
