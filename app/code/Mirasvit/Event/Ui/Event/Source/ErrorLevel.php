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



namespace Mirasvit\Event\Ui\Event\Source;


use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

class ErrorLevel implements OptionSourceInterface
{
    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $level => $label) {
            $options[] = [
                'value' => $level,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get available error levels.
     *
     * [level => label]
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            Logger::DEBUG     => __('Debug'),
            Logger::INFO      => __('Info'),
            Logger::NOTICE    => __('Notice'),
            Logger::WARNING   => __('Warning'),
            Logger::ERROR     => __('Error'),
            Logger::CRITICAL  => __('Critical'),
            Logger::ALERT     => __('Alert'),
            Logger::EMERGENCY => __('Emergency'),
        ];
    }
}
