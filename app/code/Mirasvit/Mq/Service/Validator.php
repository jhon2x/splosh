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



namespace Mirasvit\Mq\Service;


use Mirasvit\Core\Service\AbstractValidator;

class Validator extends AbstractValidator
{
    /**
     * Module requires 'exec' function to be enabled in cron environment.
     *
     * @return string[]
     */
    public function testExecFunction()
    {
        $result = self::SUCCESS;
        $title  = 'Message Queue (MQ): PHP "exec" function enabled';
        $description = [];

        $disabledFunctions = explode(',', ini_get('disable_functions'));
        if (in_array('exec', $disabledFunctions)) {
            $result = self::FAILED;
            $description[] = 'PHP "exec" function is disabled.';
            $description[] = 'Please refer to our manual for more information about this problem:';
            $description[] = 'https://docs.mirasvit.com/module-notificator/current/troubleshooting';
        }

        return [$result, $title, $description];
    }
}
