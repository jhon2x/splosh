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



namespace Mirasvit\Event\Service;

use Magento\Framework\FlagFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class TimeService
{
    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        FlagFactory $flagFactory,
        DateTime $dateTime
    ) {
        $this->flagFactory = $flagFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $flagCode
     * @return int
     */
    public function getFlagTimestamp($flagCode)
    {
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => 'event_time|' . $flagCode]])
            ->loadSelf();

        $ts = $flag->getFlagData();
        if (!$ts) {
            $ts = $this->dateTime->gmtTimestamp();
        }

        return $ts;
    }

    /**
     * @param string $flagCode
     * @return string
     */
    public function getFlagDateTime($flagCode)
    {
        return (new \DateTime())->setTimestamp($this->getFlagTimestamp($flagCode))
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @param string $flagCode
     * @return int
     */
    public function setFlagTimestamp($flagCode)
    {
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => 'event_time|' . $flagCode]])
            ->loadSelf();

        $ts = $this->dateTime->gmtTimestamp();
        $flag->setFlagData($ts)
            ->save();

        return $ts;
    }

    /**
     * @param int $shift
     * @param int|bool $timestamp
     * @return string
     */
    public function shiftDateTime($shift, $timestamp = false)
    {
        if (!$timestamp) {
            $timestamp = (new \DateTime())->getTimestamp();
        }

        return (new \DateTime())->setTimestamp($timestamp - $shift)
            ->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }
}