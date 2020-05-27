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

class TimeInterval implements ArrayInterface
{
    /**
     * Make an array of times with 1-hour intervals over a 24-hour time period.
     *
     * @param int $lower
     * @param int $upper
     * @param int $step
     * @param string $format
     *
     * @return array
     */
    private function hoursRange($lower = 0, $upper = 86400, $step = 3600, $format = 'g:ia')
    {
        $i = 0;
        $times = [];

        foreach (range($lower, $upper, $step) as $increment) {
            $increment = gmdate('H:i', $increment);

            list($hour, $minutes) = explode(':', $increment);

            $date = new \DateTime($hour . ':' . $minutes);

            //$times[(string) $increment] = $date->format($format);
            $times[$i] = strtoupper($date->format($format));
            $i++;
        }

        return $times;
    }

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->hoursRange() as $key => $hour) {
            if ($key < 24) {
                $options[] = [
                    'value' => $key,
                    'label' => $hour,
                ];
            }
        }

        return $options;
    }
}
