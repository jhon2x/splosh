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

class FlagService
{
    /**
     * @var FlagFactory
     */
    private $flagFactory;

    public function __construct(
        FlagFactory $flagFactory
    ) {
        $this->flagFactory = $flagFactory;
    }

    /**
     * @param string $flagCode
     * @return string
     */
    public function get($flagCode)
    {
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => 'event_flag|' . $flagCode]])
            ->loadSelf();

        $value = $flag->getFlagData();

        return $value;
    }

    /**
     * @param string $flagCode
     * @param string $value
     * @return $this
     */
    public function set($flagCode, $value)
    {
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => 'event_flag|' . $flagCode]])
            ->loadSelf();

        $flag->setFlagData($value)
            ->save();

        return $this;
    }
}