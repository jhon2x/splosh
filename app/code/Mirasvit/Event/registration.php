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


$registration = dirname(dirname(dirname(__DIR__))) . '/vendor/mirasvit/module-event/src/Event/registration.php';
if (file_exists($registration)) {
    # module was already installed via composer
    return;
}
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_Event',
    __DIR__
);

//$oldErrorHandler = set_error_handler(['\Mirasvit\Event\Pool\App\ErrorEvent', 'errorHandler']);
//$oldExceptionHandler = set_exception_handler(['\Mirasvit\Event\Pool\App\ErrorEvent', 'exceptionHandler']);
//register_shutdown_function(['\Mirasvit\Event\Pool\App\ErrorEvent', 'shutdownHandler']);