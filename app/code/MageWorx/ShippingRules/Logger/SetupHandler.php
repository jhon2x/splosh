<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

/**
 * Class SetupLogger
 */
class SetupHandler extends Base
{
    /**
     * Base path for all logs
     */
    const BASE_MAGEWORX_LOG_PATH = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'mageworx';

    /**
     * Name with base mageworx logs path
     *
     * @var string
     */
    protected $fileName = self::BASE_MAGEWORX_LOG_PATH . DIRECTORY_SEPARATOR . 'custom.log';

    protected $loggerType = MonologLogger::DEBUG;
}