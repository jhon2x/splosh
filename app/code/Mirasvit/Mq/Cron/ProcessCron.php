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
 * @package   mirasvit/module-message-queue
 * @version   1.0.9
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Mq\Cron;

use Magento\Framework\Shell;

class ProcessCron
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var string
     */
    private $functionCallPath;

    public function __construct(
        Shell $shell
    ) {
        $this->shell = $shell;
        $this->functionCallPath =
            PHP_BINARY . ' -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    public function execute()
    {
        $this->shell->execute("{$this->functionCallPath} mirasvit:message-queue:subscribe > /dev/null &");
    }
}