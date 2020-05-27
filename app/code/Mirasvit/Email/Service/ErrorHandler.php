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



namespace Mirasvit\Email\Service;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Service\ErrorHandlerInterface;
use Mirasvit\Email\Controller\RegistryConstants;
use Mirasvit\EmailDesigner\Api\Data\TemplateInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var UrlInterface
     */
    private $urlManager;

    public function __construct(
        Registry $registry,
        UrlInterface $urlManager
    ) {
        $this->registry = $registry;
        $this->urlManager = $urlManager;
    }

    /**
     * Register shutdown and error handler functions.
     */
    public function registerErrorHandler()
    {
        register_shutdown_function([$this, 'shutdownFunction']);
        set_error_handler([$this, 'errorHandler']);
    }

    /**
     * Restore original error handler.
     */
    public function restoreErrorHandler()
    {
        restore_error_handler();
    }

    /**
     * Call email error handler if error is fatal.
     */
    public function shutdownFunction()
    {
        $error = error_get_last();
        if (is_array($error) && $error['type'] == E_ERROR) {
            $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Assign status "Error" to current queue.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     *
     * @return bool
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (stripos($errfile, 'email-designer') !== false
            || stripos($errfile, 'email_designer') !== false
        ) {
            /** @var QueueInterface $queue */
            $queue = $this->registry->registry(RegistryConstants::CURRENT_QUEUE);
            if ($queue) {
                $content = file_get_contents($errfile);
                $template = $queue->getTemplate();
                $areaCode = ucfirst($template->getAreaCodeByContent($content));
                $templateUrl = $this->urlManager->getUrl(
                    'email_designer/template/edit',
                    [TemplateInterface::ID => $template->getId()]
                );

                $message = __(
                    '
                        This error is related to improper use of variables in the email template. Error Details:
                            <b>Email template:</b> <a target="_blank" href="%1">%2</a> (ID: %3);
                            <b>Area</b>: "%4";
                            <b>line</b>: #%5;
                            <b>Error message:</b> "%6".
                    ',
                    $templateUrl,
                    $template->getTitle(),
                    $template->getId(),
                    $areaCode,
                    $errline,
                    $errstr
                );

                $queue->error($message);

                return true;
            }
        }

        return false;
    }
}
